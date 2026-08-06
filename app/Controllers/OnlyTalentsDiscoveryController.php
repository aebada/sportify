<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\DiscoveryProfileRepository;
use App\Models\TalentCategoryRepository;
use App\Models\TalentDiscoveryRepository;
use App\Models\CommunityRepository;
use App\Models\CourseRepository;
use App\Models\TalentContentRepository;
use App\Models\TalentShortLikeRepository;
use App\Models\TalentShortRepository;
use App\Services\OtCategoryStatsService;
use App\Services\OtLandingShortsService;
use App\Services\TalentDiscoveryService;
use App\Services\TalentShortsService;
use App\Support\OnlyTalentsContext;

class OnlyTalentsDiscoveryController extends Controller
{
    // deploy-marker:20260707-ot-immersive-v5
    protected function otView(string $view, array $data = []): string
    {
        return $this->view($view, $data, 'onlytalents');
    }

    public function home(): string
    {
        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
        if (preg_match('#^/only-talents/home/?$#i', $path)) {
            return $this->homeLegacyRedirect();
        }

        if ($r = $this->requireDb()) {
            return $r;
        }

        $filter = (string) App::$request->input('filter', OtLandingShortsService::defaultFilter());
        $allowed = OtLandingShortsService::filters();
        if (!in_array($filter, $allowed, true)) {
            $filter = OtLandingShortsService::defaultFilter();
        }
        $page = max(1, (int) App::$request->input('page', 1));
        $userId = Auth::check() ? (int) Auth::id() : null;
        $category = trim((string) App::$request->input('category', ''));

        return $this->otView('pages.onlytalents.home-20260703', [
            'title'         => __('ot.home.shorts_title'),
            'metaDesc'      => __('ot.home.shorts_sub'),
            'tiktokLanding' => true,
            'feed'          => OtLandingShortsService::browse($filter, $page, OtLandingShortsService::PER_PAGE, $userId, $category !== '' ? $category : null),
            'filter'        => $filter,
            'filters'       => $allowed,
            'category'      => $category,
            'lastSynced'    => TalentShortsService::lastSynced(),
            'syncedToday'   => TalentShortsService::isSyncedToday(),
        ]);
    }

    /** Permanent redirect from legacy /only-talents/home URL. */
    public function homeLegacyRedirect(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/only-talents/home');
        $qs = parse_url($uri, PHP_URL_QUERY);
        $target = '/only-talents';
        if (is_string($qs) && $qs !== '') {
            $target .= '?' . $qs;
        }
        return $this->redirect($target, 301);
    }

    public function shortsLoadMore(): string
    {
        header('Content-Type: application/json');
        if ($r = $this->requireDb()) {
            http_response_code(503);
            return json_encode(['error' => 'db']);
        }

        $filter = (string) App::$request->input('filter', OtLandingShortsService::defaultFilter());
        $page = max(1, (int) App::$request->input('page', 1));
        $userId = Auth::check() ? (int) Auth::id() : null;
        $category = trim((string) App::$request->input('category', ''));
        $feed = OtLandingShortsService::browse($filter, $page, OtLandingShortsService::PER_PAGE, $userId, $category !== '' ? $category : null);

        return json_encode([
            'items'    => $feed['items'],
            'page'     => $feed['page'],
            'pages'    => $feed['pages'],
            'total'    => $feed['total'],
            'per_page' => $feed['per_page'],
        ]);
    }

    public function shortsLike(int $id): string
    {
        header('Content-Type: application/json');
        if (!Auth::check()) {
            http_response_code(401);
            return json_encode(['error' => 'auth']);
        }
        if (!Database::available()) {
            http_response_code(503);
            return json_encode(['error' => 'db']);
        }
        if (!Csrf::verify(App::$request->input('_csrf'))) {
            http_response_code(403);
            return json_encode(['error' => 'csrf']);
        }

        $result = TalentShortLikeRepository::toggle($id, (int) Auth::id());
        return json_encode($result);
    }

    public function discover(): string
    {
        if ($r = $this->requireDb()) {
            return $r;
        }

        $input = App::$request->query;
        $result = TalentDiscoveryService::discover($input);

        return $this->otView('pages.onlytalents.discover', [
            'title'    => __('ot.discover.title'),
            'metaDesc' => __('ot.discover.sub'),
            'result'   => $result,
            'options'  => TalentDiscoveryService::filterOptions(),
            'query'    => $input,
        ]);
    }

    public function categories(): string
    {
        if ($r = $this->requireDb()) {
            return $r;
        }

        return $this->otView('pages.onlytalents.categories', [
            'title'       => __('ot.categories.title'),
            'cards'       => OtCategoryStatsService::browseCards(),
            'roots'       => OtCategoryStatsService::talentRootCards(),
            'categories'  => TalentCategoryRepository::all(),
        ]);
    }

    public function category(string $slug): string
    {
        if ($r = $this->requireDb()) {
            return $r;
        }

        $shortsSlugs = OtCategoryStatsService::shortsCategorySlugs();
        $isShortsCategory = in_array($slug, $shortsSlugs, true);
        $cat = TalentCategoryRepository::findBySlug($slug);
        if (!$cat && !$isShortsCategory) {
            return $this->abort(404, __('ot.categories.not_found'));
        }

        $discoverSlug = $isShortsCategory
            ? OtCategoryStatsService::discoverSlugForShorts($slug)
            : $slug;
        $result = TalentDiscoveryRepository::search(['category' => $discoverSlug], max(1, (int) App::$request->input('page', 1)));
        $shorts = $isShortsCategory
            ? OtLandingShortsService::browse('all', 1, 12, Auth::check() ? (int) Auth::id() : null, $slug)
            : ['items' => [], 'total' => 0];

        $label = $isShortsCategory
            ? TalentShortRepository::categoryLabel($slug)
            : ($cat ? ot_category_name($cat) : $slug);

        return $this->otView('pages.onlytalents.category', [
            'title'            => $label,
            'category'         => $cat,
            'slug'             => $slug,
            'label'            => $label,
            'isShortsCategory' => $isShortsCategory,
            'result'           => $result,
            'shorts'           => $shorts,
        ]);
    }

    public function locations(): string
    {
        $locations = TalentDiscoveryRepository::locationCounts(24);

        return $this->otView('pages.onlytalents.locations', [
            'title'     => __('ot.locations.title'),
            'locations' => $locations,
        ]);
    }

    public function videos(): string
    {
        $sections = TalentDiscoveryService::homeSections();

        return $this->otView('pages.onlytalents.videos', [
            'title'  => __('ot.videos.title'),
            'videos' => $sections['videos'],
        ]);
    }

    public function profile(string $slug): string
    {
        if ($r = $this->requireDb()) {
            return $r;
        }

        $talent = TalentDiscoveryRepository::findBySlug($slug);
        if (!$talent) {
            return $this->abort(404, __('ot.profile.not_found'));
        }

        if (!empty($talent['source_type']) && !empty($talent['source_id'])) {
            DiscoveryProfileRepository::incrementViews((string) $talent['source_type'], (int) $talent['source_id']);
        }

        $userId = (int) ($talent['user_id'] ?? 0);
        $contentTab = (string) App::$request->input('content', 'free');
        $freeContent = $userId ? TalentContentRepository::forUser($userId, 'free', 12) : [];
        $premiumContent = $userId ? TalentContentRepository::forUser($userId, 'premium', 12) : [];
        $communities = $userId ? CommunityRepository::forUserProfile($userId) : [];
        $courses = $userId ? CourseRepository::forOwnerProfile($userId) : [];

        return $this->otView('pages.onlytalents.profile', [
            'title'           => ($talent['name'] ?? $slug) . ' · OnlyTalents',
            'talent'          => $talent,
            'communities'     => $communities,
            'courses'         => $courses,
            'freeContent'     => $freeContent,
            'premiumContent'  => $premiumContent,
            'contentTab'      => $contentTab,
        ]);
    }

    public function profileEdit(): string
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }
        if ($r = $this->requireDb()) {
            return $r;
        }

        $user = Auth::user();
        $userId = (int) ($user['id'] ?? 0);

        return $this->otView('pages.onlytalents.profile-edit', [
            'title'   => __('ot.profile.edit_title'),
            'options' => TalentDiscoveryService::filterOptions(),
            'user'    => $user,
            'profile' => DiscoveryProfileRepository::findBySource('member', $userId),
        ]);
    }

    public function profileUpdate(): string
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }
        if ($r = $this->requireDb()) {
            return $r;
        }
        if (!Csrf::verify()) {
            flash('error', __('errors.csrf'));
            return $this->redirect(ot_route('ot.profile.edit'));
        }

        $req = App::$request;
        $userId = (int) Auth::id();
        $languages = array_filter(array_map('trim', explode(',', (string) $req->input('languages', 'EN'))));
        $categories = array_filter(array_map('trim', (array) ($req->input('categories') ?? [])));
        $skills = array_filter(array_map('trim', explode(',', (string) $req->input('skills', ''))));
        $availability = array_filter(array_map('trim', (array) ($req->input('availability') ?? [])));

        DiscoveryProfileRepository::upsert('member', $userId, [
            'user_id'          => $userId,
            'talent_type'      => (string) $req->input('talent_type', 'freelancer'),
            'sport_type'       => (string) $req->input('sport_type', 'football'),
            'online_status'    => (string) $req->input('online_status', 'offline'),
            'experience_level' => (string) $req->input('experience_level', ''),
            'gender'           => (string) $req->input('gender', ''),
            'age'              => (int) $req->input('age', 0) ?: null,
            'country'          => trim((string) $req->input('country', '')),
            'city'             => trim((string) $req->input('city', '')),
            'region'           => trim((string) $req->input('region', '')),
            'languages'        => $languages ?: ['EN'],
            'categories'       => $categories,
            'skills'           => $skills,
            'availability'     => $availability,
            'last_active_at'   => date('Y-m-d H:i:s'),
        ]);

        flash('success', __('ot.profile.saved'));
        return $this->redirect(ot_route('ot.profile.edit'));
    }
}
