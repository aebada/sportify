<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\CrmActivityRepository;
use App\Models\CrmCampaignRepository;
use App\Models\CrmContactRepository;
use App\Models\User;

class CrmController extends Controller
{
    protected function guard(): ?string
    {
        if ($r = $this->requirePermission('crm.access')) {
            return $r;
        }
        return $this->requireDb();
    }

    public function dashboard(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $pipeline = CrmContactRepository::pipelineStats();
        $total = array_sum($pipeline);
        $converted = $pipeline['converted'] ?? 0;
        $funnel = [
            'new'       => $pipeline['new'] ?? 0,
            'contacted' => $pipeline['contacted'] ?? 0,
            'qualified' => $pipeline['qualified'] ?? 0,
            'converted' => $converted,
            'lost'      => $pipeline['lost'] ?? 0,
        ];

        return $this->view('admin.crm.dashboard', [
            'title'       => __('crm.title'),
            'pipeline'    => $pipeline,
            'funnel'      => $funnel,
            'total'       => $total,
            'conversion'  => $total > 0 ? round(($converted / $total) * 100, 1) : 0,
            'recent'      => CrmContactRepository::recent(10),
            'campaigns'   => array_slice(CrmCampaignRepository::all(), 0, 5),
        ], 'admin');
    }

    public function contacts(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $req = App::$request;
        $filters = [
            'q'           => trim((string) $req->input('q', '')),
            'status'      => (string) $req->input('status', ''),
            'source'      => (string) $req->input('source', ''),
            'campaign_id' => (string) $req->input('campaign_id', ''),
        ];

        return $this->view('admin.crm.contacts', [
            'title'     => __('crm.contacts'),
            'contacts'  => CrmContactRepository::all($filters),
            'filters'   => $filters,
            'campaigns' => CrmCampaignRepository::all(),
        ], 'admin');
    }

    public function exportContacts(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $req = App::$request;
        $filters = [
            'q'      => trim((string) $req->input('q', '')),
            'status' => (string) $req->input('status', ''),
            'source' => (string) $req->input('source', ''),
        ];
        $rows = CrmContactRepository::exportRows($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="sportify-contacts-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['id', 'name', 'email', 'phone', 'source', 'status', 'campaign', 'referral_code', 'tags', 'assigned_to', 'user_id', 'created_at']);
        foreach ($rows as $c) {
            fputcsv($out, [
                $c['id'],
                $c['name'],
                $c['email'] ?? '',
                $c['phone'] ?? '',
                $c['source'],
                $c['status'],
                $c['campaign_name'] ?? '',
                $c['referral_code'] ?? '',
                implode(';', $c['tags_list'] ?? []),
                $c['assigned_name'] ?? '',
                $c['user_id'] ?? '',
                $c['created_at'] ?? '',
            ]);
        }
        fclose($out);
        return '';
    }

    public function showContact(int $id): string
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $contact = CrmContactRepository::find($id);
        if (!$contact) {
            return $this->abort(404, 'Contact not found');
        }

        return $this->view('admin.crm.contact-show', [
            'title'      => $contact['name'],
            'contact'    => $contact,
            'activities' => CrmActivityRepository::forContact($id),
            'campaigns'  => CrmCampaignRepository::all(),
            'assignees'  => $this->assignees(),
        ], 'admin');
    }

    public function createContactForm(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }

        return $this->view('admin.crm.contact-form', [
            'title'     => __('crm.new_contact'),
            'contact'   => null,
            'campaigns' => CrmCampaignRepository::all(),
            'assignees' => $this->assignees(),
        ], 'admin');
    }

    public function storeContact(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        if (!Csrf::verify(App::$request->input('_csrf'))) {
            flash('error', __('crm.session_expired'));
            return $this->redirect(route('admin.crm.contacts.new'));
        }

        $data = $this->contactDataFromRequest();
        $id = CrmContactRepository::create($data);
        if (!$id) {
            flash('error', __('crm.save_failed'));
            return $this->redirect(route('admin.crm.contacts.new'));
        }

        CrmActivityRepository::log($id, Auth::id(), 'note', 'Contact created', 'Lead added manually.');
        flash('success', __('crm.contact_saved'));
        return $this->redirect(route('admin.crm.contacts.show', ['id' => $id]));
    }

    public function editContactForm(int $id): string
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $contact = CrmContactRepository::find($id);
        if (!$contact) {
            return $this->abort(404, 'Contact not found');
        }

        return $this->view('admin.crm.contact-form', [
            'title'     => __('crm.edit_contact'),
            'contact'   => $contact,
            'campaigns' => CrmCampaignRepository::all(),
            'assignees' => $this->assignees(),
        ], 'admin');
    }

    public function updateContact(int $id): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        if (!Csrf::verify(App::$request->input('_csrf'))) {
            flash('error', __('crm.session_expired'));
            return $this->redirect(route('admin.crm.contacts.edit', ['id' => $id]));
        }

        $existing = CrmContactRepository::find($id);
        if (!$existing) {
            return $this->abort(404, 'Contact not found');
        }

        $data = $this->contactDataFromRequest();
        $oldStatus = $existing['status'];
        CrmContactRepository::update($id, $data);

        if ($oldStatus !== $data['status']) {
            CrmActivityRepository::log(
                $id,
                Auth::id(),
                'status_change',
                'Status changed',
                $oldStatus . ' → ' . $data['status'],
                ['from' => $oldStatus, 'to' => $data['status']]
            );
        }

        flash('success', __('crm.contact_saved'));
        return $this->redirect(route('admin.crm.contacts.show', ['id' => $id]));
    }

    public function addActivity(int $id): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        if (!Csrf::verify(App::$request->input('_csrf'))) {
            flash('error', __('crm.session_expired'));
            return $this->redirect(route('admin.crm.contacts.show', ['id' => $id]));
        }

        if (!CrmContactRepository::find($id)) {
            return $this->abort(404, 'Contact not found');
        }

        $req = App::$request;
        $type = (string) $req->input('type', 'note');
        $body = trim((string) $req->input('body', ''));
        $subject = trim((string) $req->input('subject', ''));

        if ($body === '') {
            flash('error', __('crm.activity_required'));
            return $this->redirect(route('admin.crm.contacts.show', ['id' => $id]));
        }

        CrmActivityRepository::log($id, Auth::id(), $type, $subject ?: ucfirst($type), $body);
        flash('success', __('crm.activity_added'));
        return $this->redirect(route('admin.crm.contacts.show', ['id' => $id]));
    }

    public function campaigns(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $items = CrmCampaignRepository::all();
        foreach ($items as &$c) {
            $c['contact_count'] = CrmCampaignRepository::contactCount((int) $c['id']);
        }

        return $this->view('admin.crm.campaigns', [
            'title'     => __('crm.campaigns'),
            'campaigns' => $items,
        ], 'admin');
    }

    public function createCampaignForm(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }

        return $this->view('admin.crm.campaign-form', [
            'title'    => __('crm.new_campaign'),
            'campaign' => null,
        ], 'admin');
    }

    public function storeCampaign(): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        if (!Csrf::verify(App::$request->input('_csrf'))) {
            flash('error', __('crm.session_expired'));
            return $this->redirect(route('admin.crm.campaigns.new'));
        }

        $data = $this->campaignDataFromRequest();
        $id = CrmCampaignRepository::create($data);
        if (!$id) {
            flash('error', __('crm.save_failed'));
            return $this->redirect(route('admin.crm.campaigns.new'));
        }
        flash('success', __('crm.campaign_saved'));
        return $this->redirect(route('admin.crm.campaigns.edit', ['id' => $id]));
    }

    public function editCampaignForm(int $id): string
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $campaign = CrmCampaignRepository::find($id);
        if (!$campaign) {
            return $this->abort(404, 'Campaign not found');
        }

        return $this->view('admin.crm.campaign-form', [
            'title'    => __('crm.edit_campaign'),
            'campaign' => $campaign,
        ], 'admin');
    }

    public function updateCampaign(int $id): string
    {
        if ($r = $this->guard()) {
            return $r;
        }
        if (!Csrf::verify(App::$request->input('_csrf'))) {
            flash('error', __('crm.session_expired'));
            return $this->redirect(route('admin.crm.campaigns.edit', ['id' => $id]));
        }

        if (!CrmCampaignRepository::find($id)) {
            return $this->abort(404, 'Campaign not found');
        }

        CrmCampaignRepository::update($id, $this->campaignDataFromRequest());
        flash('success', __('crm.campaign_saved'));
        return $this->redirect(route('admin.crm.campaigns'));
    }

    protected function contactDataFromRequest(): array
    {
        $req = App::$request;
        $tags = $req->input('tags');
        if (!is_array($tags)) {
            $tags = [];
        }

        return [
            'name'           => trim((string) $req->input('name', '')),
            'email'          => trim((string) $req->input('email', '')),
            'phone'          => trim((string) $req->input('phone', '')),
            'source'         => (string) $req->input('source', 'website'),
            'status'         => (string) $req->input('status', 'new'),
            'notes'          => trim((string) $req->input('notes', '')),
            'assigned_to'    => $req->input('assigned_to'),
            'user_id'        => $req->input('user_id'),
            'campaign_id'    => $req->input('campaign_id'),
            'referral_code'  => trim((string) $req->input('referral_code', '')),
            'tags'           => $tags,
        ];
    }

    protected function campaignDataFromRequest(): array
    {
        $req = App::$request;
        $budget = trim((string) $req->input('budget', ''));
        $budgetCents = $budget !== '' ? (int) round((float) $budget * 100) : null;

        return [
            'name'          => trim((string) $req->input('name', '')),
            'type'          => (string) $req->input('type', 'email'),
            'status'        => (string) $req->input('status', 'draft'),
            'start_date'    => (string) $req->input('start_date', ''),
            'end_date'      => (string) $req->input('end_date', ''),
            'utm_source'    => trim((string) $req->input('utm_source', '')),
            'utm_medium'    => trim((string) $req->input('utm_medium', '')),
            'utm_campaign'  => trim((string) $req->input('utm_campaign', '')),
            'budget_cents'  => $budgetCents,
            'notes'         => trim((string) $req->input('notes', '')),
        ];
    }

    protected function assignees(): array
    {
        if (!Database::available()) {
            return [];
        }
        return Database::select(
            "SELECT id, name, role FROM users WHERE role IN ('admin', 'scout') ORDER BY name"
        );
    }
}
