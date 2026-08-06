<?php
/**
 * Route definitions. Laravel-style: $router->get('/path', [Controller::class, 'method'], 'name')
 */

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\FacebookOAuthController;
use App\Controllers\OAuthController;
use App\Controllers\PlayerController;
use App\Controllers\SearchController;
use App\Controllers\ClubController;
use App\Controllers\HighlightsController;
use App\Controllers\NewsController;
use App\Controllers\TransferController;
use App\Controllers\FitPassController;
use App\Controllers\MembershipController;
use App\Controllers\AdminMembershipController;
use App\Controllers\PageController;
use App\Controllers\AdminController;
use App\Controllers\AdminPlayerController;
use App\Controllers\ApiController;
use App\Controllers\ChatbotController;
use App\Controllers\FeedController;
use App\Controllers\MemberController;
use App\Controllers\MessageController;
use App\Controllers\BillingController;
use App\Controllers\WebhookController;
use App\Controllers\PaymentController;
use App\Controllers\ProviderController;
use App\Controllers\VideosController;
use App\Controllers\CompetitionController;
use App\Controllers\MatchController;
use App\Controllers\ScoutController;
use App\Controllers\TrainerController;
use App\Controllers\CrmController;
use App\Controllers\ReferralController;
use App\Controllers\PlayerAnalysisController;
use App\Controllers\StandingsController;
use App\Controllers\TeamController;
use App\Controllers\TeamFanChatController;
use App\Controllers\StatsController;
use App\Controllers\TournamentController;
use App\Controllers\RankingsController;
use App\Controllers\TvScheduleController;
use App\Controllers\FixturesController;
use App\Controllers\ChatGroupController;
use App\Controllers\DirectMessageController;
use App\Controllers\MarketplaceController;
use App\Controllers\VendorController;
use App\Controllers\AdminMarketplaceController;
use App\Controllers\FanPageController;
use App\Controllers\FanTopicController;
use App\Controllers\OnlyTalentsController;
use App\Controllers\OnlyTalentsDiscoveryController;
use App\Controllers\AdminTalentCategoryController;
use App\Controllers\AdminDiscoveryController;
use App\Controllers\AdminSocialAuthController;
use App\Controllers\OtCommunityController;
use App\Controllers\BookingController;
use App\Controllers\BookingProviderController;
use App\Controllers\AdminBookingController;
use App\Controllers\ProCoachController;
use App\Controllers\ProCoachDashboardController;
use App\Controllers\AdminProCoachController;
use App\Controllers\TransferBureauController;
use App\Controllers\AdminTransferBureauController;
use App\Controllers\MarktController;
use App\Controllers\PlayController;
use App\Controllers\WorldCupController;
use App\Controllers\WellnessController;

/** @var \App\Core\Router $router */
$router->get('/assets/img/logo.svg', [PageController::class, 'logoSvg'], 'assets.logo');
$router->get('/assets/img/logo.png', [PageController::class, 'logoPng'], 'assets.logo_png');
$router->get('/assets/img/logo-icon.png', [PageController::class, 'logoIconPng'], 'assets.logo_icon_png');
$router->get('/assets/img/favicon.png', [PageController::class, 'faviconPng'], 'assets.favicon_png');
$router->get('/opcache-purge.php', [PageController::class, 'opcachePurge'], 'deploy.opcache');
$router->get('/deploy-check', [PageController::class, 'deployCheck'], 'deploy.check');
$router->get('/api/brand-mark', [ApiController::class, 'logo'], 'api.logo');
$router->get('/', [HomeController::class, 'index'], 'home');
$router->get('/world-cup', [WorldCupController::class, 'index'], 'world_cup.hub');
$router->get('/switch-locale/{locale}', [HomeController::class, 'setLocale'], 'locale.switch');
$router->get('/lang/{locale}', [HomeController::class, 'setLocale'], 'lang.switch');

// Players & discovery
$router->get('/players', [PlayerController::class, 'index'], 'players.index');
$router->get('/players/{slug}', [PlayerController::class, 'show'], 'players.show');
$router->get('/players/{slug}/markt', [MarktController::class, 'playerMarkt'], 'players.markt');
$router->get('/players/{slug}/analysis', [PlayerAnalysisController::class, 'show'], 'players.analysis');
$router->post('/players/{slug}/analysis/regenerate', [PlayerAnalysisController::class, 'regenerate'], 'players.analysis.regenerate');
$router->post('/players/{slug}/health-records', [PlayerAnalysisController::class, 'storeHealth'], 'players.health.store');
$router->get('/search', [SearchController::class, 'index'], 'search');

// Clubs
$router->get('/clubs/dashboard', [ClubController::class, 'dashboard'], 'club.dashboard');
$router->get('/clubs/recommendations', [ClubController::class, 'recommendations'], 'club.recommendations');

// Scouts
$router->get('/scout', [ScoutController::class, 'index'], 'scout.index');
$router->get('/scout/dashboard', [ScoutController::class, 'dashboard'], 'scout.dashboard');
$router->get('/scout/players/create', [ScoutController::class, 'createForm'], 'scout.players.create');
$router->post('/scout/players', [ScoutController::class, 'store'], 'scout.players.store');
$router->get('/scout/players/bulk', [ScoutController::class, 'bulkForm'], 'scout.players.bulk');
$router->post('/scout/players/bulk', [ScoutController::class, 'bulkStore'], 'scout.players.bulk.store');
$router->get('/scout/players/link', [ScoutController::class, 'linkForm'], 'scout.players.link');
$router->post('/scout/players/link', [ScoutController::class, 'linkStore'], 'scout.players.link.store');
$router->post('/scout/gdpr/accept', [ScoutController::class, 'acceptGdpr'], 'scout.gdpr.accept');
$router->get('/scout/players/{id}/edit', [ScoutController::class, 'editForm'], 'scout.players.edit');
$router->post('/scout/players/{id}', [ScoutController::class, 'update'], 'scout.players.update');
$router->post('/scout/players/{id}/delete', [ScoutController::class, 'destroy'], 'scout.players.delete');
$router->post('/scout/shortlist/add', [ScoutController::class, 'addShortlist'], 'scout.shortlist.add');
$router->post('/scout/shortlist/remove', [ScoutController::class, 'removeShortlist'], 'scout.shortlist.remove');

$router->get('/trainer', [TrainerController::class, 'index'], 'trainer.index');
$router->get('/trainer/dashboard', [TrainerController::class, 'dashboard'], 'trainer.dashboard');
$router->get('/trainers', [TrainerController::class, 'listing'], 'trainers.index');

// Content
$router->get('/highlights', [HighlightsController::class, 'index'], 'highlights');
$router->get('/live-streams', [TvScheduleController::class, 'liveStreams'], 'live_streams.index');
$router->get('/live-sports', [TvScheduleController::class, 'liveStreams'], 'live_streams.index_legacy');
$router->get('/videos', [VideosController::class, 'index'], 'videos');
$router->get('/news', [NewsController::class, 'index'], 'news');
$router->get('/news/{slug}', [NewsController::class, 'show'], 'news.show');
$router->get('/transfers', [TransferController::class, 'index'], 'transfers');

// Sportify Markt — Transfermarkt-style pro market intelligence (distinct from /transfers news & /transfer-bureau)
$router->get('/markt', [MarktController::class, 'index'], 'markt.index');
$router->get('/markt/transfers', [MarktController::class, 'transfers'], 'markt.transfers');
$router->get('/markt/rumours', [MarktController::class, 'rumours'], 'markt.rumours');
$router->get('/markt/werte', [MarktController::class, 'werte'], 'markt.werte');
$router->get('/markt/vertraege', [MarktController::class, 'vertraege'], 'markt.vertraege');
$router->get('/markt/verein/{slug}', [MarktController::class, 'verein'], 'markt.verein');
$router->get('/markt/vergleich', [MarktController::class, 'vergleich'], 'markt.vergleich');

// Sportify Transfer Bureau (amateur transfer marketplace — distinct from pro /transfers news)
$router->get('/transfer-bureau', [TransferBureauController::class, 'index'], 'transfer_bureau');
$router->get('/vereinsmarkt', [TransferBureauController::class, 'index'], 'transfer_bureau.de');
$router->get('/transfer-bureau/search/players', [TransferBureauController::class, 'searchPlayers'], 'transfer_bureau.search.players');
$router->get('/transfer-bureau/search/clubs', [TransferBureauController::class, 'searchClubs'], 'transfer_bureau.search.clubs');
$router->get('/transfer-bureau/search/trainers', [TransferBureauController::class, 'searchTrainers'], 'transfer_bureau.search.trainers');
$router->get('/transfer-bureau/listings', [TransferBureauController::class, 'listings'], 'transfer_bureau.listings');
$router->get('/transfer-bureau/listings/create', [TransferBureauController::class, 'createListingForm'], 'transfer_bureau.listings.create');
$router->post('/transfer-bureau/listings', [TransferBureauController::class, 'storeListing'], 'transfer_bureau.listings.store');
$router->get('/transfer-bureau/profile', [TransferBureauController::class, 'profileForm'], 'transfer_bureau.profile');
$router->post('/transfer-bureau/profile', [TransferBureauController::class, 'updateProfile'], 'transfer_bureau.profile.update');
$router->get('/transfer-bureau/trials', [TransferBureauController::class, 'trials'], 'transfer_bureau.trials');
$router->post('/transfer-bureau/trials', [TransferBureauController::class, 'storeTrial'], 'transfer_bureau.trials.store');
$router->post('/transfer-bureau/trials/respond', [TransferBureauController::class, 'respondTrial'], 'transfer_bureau.trials.respond');
$router->post('/transfer-bureau/contact', [TransferBureauController::class, 'contact'], 'transfer_bureau.contact');
$router->get('/transfer-bureau/premium', [TransferBureauController::class, 'premium'], 'transfer_bureau.premium');
$router->post('/transfer-bureau/premium/checkout', [TransferBureauController::class, 'premiumCheckout'], 'transfer_bureau.premium.checkout');
$router->get('/transfer-bureau/faq', [TransferBureauController::class, 'faq'], 'transfer_bureau.faq');
$router->get('/admin/transfer-bureau', [AdminTransferBureauController::class, 'index'], 'admin.transfer_bureau');
$router->post('/admin/transfer-bureau/verify', [AdminTransferBureauController::class, 'verifyClub'], 'admin.transfer_bureau.verify');
$router->post('/admin/transfer-bureau/moderate', [AdminTransferBureauController::class, 'moderateListing'], 'admin.transfer_bureau.moderate');
$router->get('/admin/fitpass-plans', [AdminMembershipController::class, 'index'], 'admin.memberships');
$router->post('/admin/fitpass-plans', [AdminMembershipController::class, 'index'], 'admin.memberships.assign');
$router->get('/admin/memberships', [AdminMembershipController::class, 'index'], 'admin.memberships.legacy');
$router->post('/admin/memberships', [AdminMembershipController::class, 'index'], 'admin.memberships.assign.legacy');

$router->get('/competitions', [CompetitionController::class, 'index'], 'competitions.index');
$router->get('/predictions', [CompetitionController::class, 'index'], 'predictions.index');
$router->get('/competitions/{slug}/leaderboard', [CompetitionController::class, 'leaderboard'], 'competitions.leaderboard');
$router->get('/competitions/{slug}', [CompetitionController::class, 'show'], 'competitions.show');
$router->post('/competitions/predict', [CompetitionController::class, 'predict'], 'competitions.predict');

// Live matches hub
$router->get('/matches', [MatchController::class, 'index'], 'matches.index');
$router->get('/live', [MatchController::class, 'live'], 'matches.live_short');
$router->get('/matches/live', [MatchController::class, 'live'], 'matches.live');
$router->get('/matches/results', [MatchController::class, 'results'], 'matches.results');
$router->get('/matches/upcoming', [MatchController::class, 'upcoming'], 'matches.upcoming');
$router->get('/matches/calendar', [FixturesController::class, 'index'], 'matches.calendar');
$router->get('/matches/calendar/{date}', [FixturesController::class, 'calendar'], 'matches.calendar.date');
$router->get('/matches/{id}/h2h', [MatchController::class, 'h2h'], 'matches.h2h');
$router->get('/matches/{id}', [MatchController::class, 'show'], 'matches.show');

// Kooora-style sports hub
$router->get('/fixtures', [FixturesController::class, 'index'], 'fixtures.index');
$router->get('/fixtures/calendar/{date}', [FixturesController::class, 'calendar'], 'fixtures.calendar');
$router->get('/tables', [StandingsController::class, 'index'], 'tables.index');
$router->get('/standings', [StandingsController::class, 'index'], 'standings.index');
$router->get('/standings/{slug}', [StandingsController::class, 'show'], 'standings.show');
$router->get('/stats', [StatsController::class, 'index'], 'stats.index');
$router->get('/stats/scorers', [StatsController::class, 'scorers'], 'stats.scorers');
$router->get('/stats/assists', [StatsController::class, 'assists'], 'stats.assists');
$router->get('/teams/{slug}', [TeamController::class, 'show'], 'teams.show');
$router->get('/teams/{slug}/chat', [TeamFanChatController::class, 'index'], 'teams.chat');
$router->get('/teams/{slug}/chat/{room}', [TeamFanChatController::class, 'show'], 'teams.chat.room');
$router->post('/teams/{slug}/chat/{room}/messages', [TeamFanChatController::class, 'postMessage'], 'teams.chat.messages');
$router->get('/tournaments', [TournamentController::class, 'index'], 'tournaments.index');
$router->get('/tournaments/{slug}', [TournamentController::class, 'show'], 'tournaments.show');
$router->get('/rankings', [RankingsController::class, 'index'], 'rankings.index');
$router->get('/tv', [TvScheduleController::class, 'index'], 'tv.index');

$router->get('/fit-pass', [FitPassController::class, 'index'], 'fitpass');
$router->get('/fit-pass/plans', [MembershipController::class, 'index'], 'fitpass.plans');
$router->get('/fit-pass/checkout/{tier}', [BillingController::class, 'checkout'], 'billing.checkout');
$router->post('/fit-pass/checkout', [BillingController::class, 'process'], 'billing.process');
$router->get('/fit-pass/success', [BillingController::class, 'success'], 'billing.success');
$router->get('/fit-pass/cancel/{tier}', [BillingController::class, 'cancel'], 'billing.cancel');

// FIT-Pass AI Wellness Assistant (Phase 1 MVP)
$router->get('/wellness', [WellnessController::class, 'dashboard'], 'wellness.dashboard');
$router->get('/wellness/goals', [WellnessController::class, 'goals'], 'wellness.goals');
$router->post('/wellness/goals', [WellnessController::class, 'saveGoals'], 'wellness.goals.save');
$router->get('/wellness/recommendations', [WellnessController::class, 'recommendations'], 'wellness.recommendations');
$router->get('/wellness/chat', [WellnessController::class, 'chat'], 'wellness.chat');
$router->post('/wellness/chat', [WellnessController::class, 'chatPost'], 'wellness.chat.post');
$router->get('/wellness/calendar', [WellnessController::class, 'calendar'], 'wellness.calendar');
$router->get('/wellness/calendar/google', [WellnessController::class, 'googleCalendarStart'], 'wellness.calendar.google');
$router->get('/wellness/calendar/google/callback', [WellnessController::class, 'googleCalendarCallback'], 'wellness.calendar.google.callback');
$router->post('/wellness/calendar/disconnect', [WellnessController::class, 'calendarDisconnect'], 'wellness.calendar.disconnect');
$router->get('/wellness/booking/prepare/{providerSlug}', [WellnessController::class, 'prepareBooking'], 'wellness.booking.prepare');
$router->post('/wellness/booking/confirm', [WellnessController::class, 'confirmBooking'], 'wellness.booking.confirm');

$router->get('/account/billing', [BillingController::class, 'account'], 'billing.account');
$router->post('/account/billing/cancel', [BillingController::class, 'cancelSubscription'], 'billing.cancel_sub');

// Role-based FIT-Pass membership tiers
$router->get('/membership', [MembershipController::class, 'index'], 'membership.index');
$router->get('/membership/{role}', [MembershipController::class, 'role'], 'membership.role');
$router->get('/membership/checkout/{plan_slug}', [MembershipController::class, 'checkout'], 'membership.checkout');
$router->post('/membership/checkout', [MembershipController::class, 'process'], 'membership.process');
$router->get('/membership/success', [MembershipController::class, 'success'], 'membership.success');
$router->get('/membership/account', [MembershipController::class, 'account'], 'membership.account');
$router->get('/membership/usage', [MembershipController::class, 'usage'], 'membership.usage');
$router->post('/membership/cancel', [MembershipController::class, 'cancelMembership'], 'membership.cancel');
$router->post('/membership/trial', [MembershipController::class, 'startTrial'], 'membership.trial');

$router->post('/webhooks/stripe', [WebhookController::class, 'stripe'], 'webhooks.stripe');
$router->post('/api/stripe/webhook', [WebhookController::class, 'stripe'], 'webhooks.stripe.api');
$router->post('/webhooks/paypal', [WebhookController::class, 'paypal'], 'webhooks.paypal');

$router->get('/payment/checkout', [PaymentController::class, 'checkout'], 'payment.checkout');
$router->post('/payment/stripe/intent', [PaymentController::class, 'stripeIntent'], 'payment.stripe.intent');
$router->post('/payment/paypal/create', [PaymentController::class, 'paypalCreate'], 'payment.paypal.create');
$router->get('/payment/paypal/return', [PaymentController::class, 'paypalReturn'], 'payment.paypal.return');
$router->get('/payment/paypal/capture', [PaymentController::class, 'paypalCapture'], 'payment.paypal.capture');
$router->get('/payment/success', [PaymentController::class, 'success'], 'payment.success');
$router->get('/payment/cancel', [PaymentController::class, 'cancel'], 'payment.cancel');

// Sport providers (gyms, massage, physio, etc.)
$router->get('/providers', [ProviderController::class, 'index'], 'providers.index');
$router->get('/providers/join', [ProviderController::class, 'joinForm'], 'providers.join');
$router->post('/providers/join', [ProviderController::class, 'join'], 'providers.join.post');
$router->get('/providers/panel', [ProviderController::class, 'panel'], 'providers.panel');
$router->post('/providers/panel/profile', [ProviderController::class, 'updateProfile'], 'providers.profile.update');
$router->post('/providers/panel/plans', [ProviderController::class, 'storePlan'], 'providers.plans.store');
$router->post('/providers/panel/plans/delete', [ProviderController::class, 'deletePlan'], 'providers.plans.delete');
$router->get('/providers/{slug}/checkout/{planSlug}', [ProviderController::class, 'checkout'], 'providers.checkout');
$router->post('/providers/checkout', [ProviderController::class, 'processCheckout'], 'providers.checkout.post');
$router->get('/providers/{slug}/success', [ProviderController::class, 'success'], 'providers.success');
$router->get('/providers/{slug}', [ProviderController::class, 'show'], 'providers.show');

// Marketplace (multivendor sports gear)
$router->get('/marketplace', [MarketplaceController::class, 'index'], 'marketplace.index');
$router->get('/marketplace/sell', [MarketplaceController::class, 'sell'], 'marketplace.sell');
$router->post('/marketplace/sell', [MarketplaceController::class, 'sellPost'], 'marketplace.sell.post');
$router->get('/marketplace/cart', [MarketplaceController::class, 'cart'], 'marketplace.cart');
$router->post('/marketplace/cart/add', [MarketplaceController::class, 'addToCart'], 'marketplace.cart.add');
$router->post('/marketplace/cart/update', [MarketplaceController::class, 'updateCart'], 'marketplace.cart.update');
$router->post('/marketplace/cart/remove', [MarketplaceController::class, 'removeFromCart'], 'marketplace.cart.remove');
$router->get('/marketplace/checkout', [MarketplaceController::class, 'checkout'], 'marketplace.checkout');
$router->post('/marketplace/checkout', [MarketplaceController::class, 'placeOrder'], 'marketplace.checkout.place');
$router->get('/marketplace/orders', [MarketplaceController::class, 'orders'], 'marketplace.orders');
$router->post('/marketplace/review', [MarketplaceController::class, 'review'], 'marketplace.review');
$router->get('/marketplace/categories/{category}', [MarketplaceController::class, 'category'], 'marketplace.category');
$router->get('/marketplace/vendor/{slug}', [MarketplaceController::class, 'vendorShop'], 'marketplace.vendor');
$router->get('/marketplace/{slug}', [MarketplaceController::class, 'show'], 'marketplace.show');

$router->get('/vendor/dashboard', [VendorController::class, 'dashboard'], 'vendor.dashboard');
$router->get('/vendor/listings/create', [VendorController::class, 'createListing'], 'vendor.listings.create');
$router->post('/vendor/listings', [VendorController::class, 'storeListing'], 'vendor.listings.store');
$router->get('/vendor/listings/{id}/edit', [VendorController::class, 'editListing'], 'vendor.listings.edit');
$router->post('/vendor/listings/{id}', [VendorController::class, 'updateListing'], 'vendor.listings.update');
$router->get('/vendor/orders', [VendorController::class, 'orders'], 'vendor.orders');
$router->post('/vendor/orders/status', [VendorController::class, 'updateOrderStatus'], 'vendor.orders.status');

$router->get('/admin/marketplace', [AdminMarketplaceController::class, 'index'], 'admin.marketplace');
$router->post('/admin/marketplace/vendors/approve', [AdminMarketplaceController::class, 'approveVendor'], 'admin.marketplace.vendor.approve');
$router->post('/admin/marketplace/vendors/suspend', [AdminMarketplaceController::class, 'suspendVendor'], 'admin.marketplace.vendor.suspend');
$router->post('/admin/marketplace/listings/feature', [AdminMarketplaceController::class, 'featureListing'], 'admin.marketplace.listing.feature');
$router->post('/admin/marketplace/listings/archive', [AdminMarketplaceController::class, 'archiveListing'], 'admin.marketplace.listing.archive');

// Fan pages & topics
$router->get('/fans', [FanPageController::class, 'index'], 'fans.index');
$router->get('/fans/create', [FanPageController::class, 'createForm'], 'fans.create');
$router->post('/fans/create', [FanPageController::class, 'create'], 'fans.create.post');
$router->get('/fans/{slug}', [FanPageController::class, 'show'], 'fans.show');
$router->post('/fans/{slug}/join', [FanPageController::class, 'join'], 'fans.join');
$router->post('/fans/{slug}/leave', [FanPageController::class, 'leave'], 'fans.leave');
$router->get('/fans/{slug}/topics', [FanTopicController::class, 'index'], 'fans.topics.index');
$router->get('/fans/{slug}/topics/{id}', [FanTopicController::class, 'show'], 'fans.topics.show');
$router->post('/fans/{slug}/topics', [FanTopicController::class, 'store'], 'fans.topics.store');
$router->post('/fans/{slug}/topics/{id}/reply', [FanTopicController::class, 'reply'], 'fans.topics.reply');

// OnlyTalents discovery + shorts (main-domain /only-talents/* paths)
$router->get('/only-talents', [OnlyTalentsDiscoveryController::class, 'home'], 'ot.home');
$router->get('/only-talents/home', [OnlyTalentsDiscoveryController::class, 'homeLegacyRedirect'], 'ot.home.legacy');
$router->get('/only-talents/discover', [OnlyTalentsDiscoveryController::class, 'discover'], 'ot.discover');
$router->get('/only-talents/categories', [OnlyTalentsDiscoveryController::class, 'categories'], 'ot.categories');
$router->get('/only-talents/categories/{slug}', [OnlyTalentsDiscoveryController::class, 'category'], 'ot.category');
$router->get('/only-talents/locations', [OnlyTalentsDiscoveryController::class, 'locations'], 'ot.locations');
$router->get('/only-talents/videos', [OnlyTalentsDiscoveryController::class, 'videos'], 'ot.videos');
$router->get('/only-talents/profile/edit', [OnlyTalentsDiscoveryController::class, 'profileEdit'], 'ot.profile.edit');
$router->post('/only-talents/profile/edit', [OnlyTalentsDiscoveryController::class, 'profileUpdate'], 'ot.profile.update');
$router->get('/only-talents/profile/{slug}', [OnlyTalentsDiscoveryController::class, 'profile'], 'ot.profile');

$router->get('/only-talents/communities', [OtCommunityController::class, 'index'], 'ot.communities');
$router->get('/only-talents/communities/create', [OtCommunityController::class, 'createForm'], 'ot.communities.create');
$router->post('/only-talents/communities', [OtCommunityController::class, 'store'], 'ot.communities.store');
$router->get('/only-talents/my/communities', [OtCommunityController::class, 'myCommunities'], 'ot.communities.my');
$router->get('/only-talents/communities/{slug}', [OtCommunityController::class, 'show'], 'ot.community.show');
$router->get('/only-talents/communities/{slug}/join', [OtCommunityController::class, 'joinForm'], 'ot.community.join');
$router->post('/only-talents/communities/{slug}/subscribe', [OtCommunityController::class, 'subscribe'], 'ot.community.subscribe');
$router->post('/only-talents/communities/{slug}/posts', [OtCommunityController::class, 'storePost'], 'ot.community.post');
$router->post('/only-talents/communities/{slug}/posts/{postId}/like', [OtCommunityController::class, 'likePost'], 'ot.community.like');
$router->get('/only-talents/communities/{slug}/courses', [OtCommunityController::class, 'courses'], 'ot.community.courses');
$router->get('/only-talents/communities/{slug}/courses/{courseSlug}', [OtCommunityController::class, 'courseShow'], 'ot.community.course');
$router->get('/only-talents/communities/{slug}/courses/{courseSlug}/lessons/{id}', [OtCommunityController::class, 'lessonShow'], 'ot.community.lesson');
$router->get('/only-talents/communities/{slug}/live', [OtCommunityController::class, 'live'], 'ot.community.live');
$router->get('/only-talents/communities/{slug}/manage', [OtCommunityController::class, 'manage'], 'ot.community.manage');
$router->post('/only-talents/communities/{slug}/courses', [OtCommunityController::class, 'storeCourse'], 'ot.community.course.store');
$router->post('/only-talents/communities/{slug}/members/action', [OtCommunityController::class, 'memberAction'], 'ot.community.member');

$router->get('/only-talents/upload', [OnlyTalentsController::class, 'uploadForm'], 'only_talents.upload');
$router->post('/only-talents/upload', [OnlyTalentsController::class, 'upload'], 'only_talents.upload.post');
$router->get('/only-talents/api/load-more', [OnlyTalentsController::class, 'loadMore'], 'only_talents.load_more');
$router->post('/only-talents/shorts/{id}/like', [OnlyTalentsController::class, 'like'], 'only_talents.like');
$router->get('/only-talents/{slug}/subscribe', [OnlyTalentsController::class, 'subscribeForm'], 'only_talents.subscribe');
$router->post('/only-talents/{slug}/subscribe', [OnlyTalentsController::class, 'subscribe'], 'only_talents.subscribe.post');
$router->get('/only-talents/{slug}', [OnlyTalentsController::class, 'show'], 'only_talents.show');

// Book-a-Talk
$router->get('/bookings', [BookingController::class, 'index'], 'bookings.index');
$router->get('/talk', [BookingController::class, 'index'], 'bookings.talk');
$router->get('/bookings/my', [BookingController::class, 'my'], 'bookings.my');
$router->post('/bookings/review', [BookingController::class, 'review'], 'bookings.review');
$router->get('/bookings/checkout', [BookingController::class, 'checkout'], 'bookings.checkout');
$router->post('/bookings/checkout', [BookingController::class, 'place'], 'bookings.checkout.place');
$router->get('/bookings/{playerSlug}', [BookingController::class, 'show'], 'bookings.show');
$router->get('/bookings/provider', [BookingProviderController::class, 'dashboard'], 'bookings.provider');
$router->post('/bookings/provider/profile', [BookingProviderController::class, 'updateProfile'], 'bookings.provider.profile');
$router->post('/bookings/provider/slots', [BookingProviderController::class, 'addSlot'], 'bookings.provider.slot.add');
$router->post('/bookings/provider/slots/delete', [BookingProviderController::class, 'deleteSlot'], 'bookings.provider.slot.delete');
$router->get('/bookings/provider/orders', [BookingProviderController::class, 'orders'], 'bookings.provider.orders');
$router->post('/bookings/provider/confirm', [BookingProviderController::class, 'confirmOrder'], 'bookings.provider.confirm');
$router->post('/bookings/provider/cancel', [BookingProviderController::class, 'cancelOrder'], 'bookings.provider.cancel');
$router->get('/admin/bookings', [AdminBookingController::class, 'index'], 'admin.bookings');
$router->post('/admin/bookings/verify', [AdminBookingController::class, 'verify'], 'admin.bookings.verify');

// Sportify Pro Coaches (training subscriptions + sessions)
$router->get('/pro-coaches', [ProCoachController::class, 'index'], 'pro_coaches.index');
$router->post('/pro-coaches/city', [ProCoachController::class, 'setCity'], 'pro_coaches.city');
$router->get('/pro-coaches/feed', [ProCoachController::class, 'feed'], 'pro_coaches.feed');
$router->get('/pro-coaches/my', [ProCoachController::class, 'mySessions'], 'pro_coaches.my_sessions');
$router->get('/pro-coaches/become-creator', [ProCoachDashboardController::class, 'becomeCreatorForm'], 'pro_coaches.become');
$router->post('/pro-coaches/become-creator', [ProCoachDashboardController::class, 'becomeCreator'], 'pro_coaches.become.post');
$router->get('/pro-coaches/dashboard', [ProCoachDashboardController::class, 'dashboard'], 'pro_coaches.dashboard');
$router->post('/pro-coaches/dashboard/profile', [ProCoachDashboardController::class, 'updateProfile'], 'pro_coaches.dashboard.profile');
$router->post('/pro-coaches/dashboard/tiers', [ProCoachDashboardController::class, 'storeTier'], 'pro_coaches.dashboard.tier');
$router->post('/pro-coaches/dashboard/tiers/delete', [ProCoachDashboardController::class, 'deleteTier'], 'pro_coaches.dashboard.tier.delete');
$router->post('/pro-coaches/dashboard/posts', [ProCoachDashboardController::class, 'storePost'], 'pro_coaches.dashboard.post');
$router->post('/pro-coaches/dashboard/offerings', [ProCoachDashboardController::class, 'storeOffering'], 'pro_coaches.dashboard.offering');
$router->post('/pro-coaches/dashboard/offerings/delete', [ProCoachDashboardController::class, 'deleteOffering'], 'pro_coaches.dashboard.offering.delete');
$router->post('/pro-coaches/dashboard/slots', [ProCoachDashboardController::class, 'addSlot'], 'pro_coaches.dashboard.slot.add');
$router->post('/pro-coaches/dashboard/slots/delete', [ProCoachDashboardController::class, 'deleteSlot'], 'pro_coaches.dashboard.slot.delete');
$router->get('/pro-coaches/{username}/subscribe', [ProCoachController::class, 'subscribeForm'], 'pro_coaches.subscribe');
$router->post('/pro-coaches/{username}/subscribe', [ProCoachController::class, 'subscribe'], 'pro_coaches.subscribe.post');
$router->get('/pro-coaches/{username}/book', [ProCoachController::class, 'bookSession'], 'pro_coaches.book');
$router->post('/pro-coaches/{username}/book', [ProCoachController::class, 'bookSessionPlace'], 'pro_coaches.book.post');
$router->get('/pro-coaches/{username}', [ProCoachController::class, 'show'], 'pro_coaches.show');
$router->get('/admin/pro-coaches', [AdminProCoachController::class, 'index'], 'admin.pro_coaches');
$router->post('/admin/pro-coaches/approve', [AdminProCoachController::class, 'approve'], 'admin.pro_coaches.approve');
$router->post('/admin/pro-coaches/feature', [AdminProCoachController::class, 'feature'], 'admin.pro_coaches.feature');
$router->post('/admin/pro-coaches/moderate', [AdminProCoachController::class, 'moderatePost'], 'admin.pro_coaches.moderate');

// API (JSON) — powers the Sportify Assistant chatbot
$router->get('/api/players', [ApiController::class, 'players'], 'api.players');
$router->get('/api/scores-ticker', [ApiController::class, 'scoresTicker'], 'api.scores_ticker');
$router->post('/api/chatbot', [ChatbotController::class, 'chat'], 'api.chatbot');
$router->post('/api/chat/groups/{id}/messages', [ChatGroupController::class, 'apiMessages'], 'api.chat.groups.messages');
$router->post('/api/chat/dm/{conversationId}/messages', [DirectMessageController::class, 'apiMessages'], 'api.chat.dm.messages');

// Sportify Assistant info page (FAB remains separate widget)
$router->get('/assistant', [PageController::class, 'assistant'], 'assistant');

// Team fan chat groups
$router->get('/chat/groups', [ChatGroupController::class, 'index'], 'chat.groups.index');
$router->get('/chat/groups/create', [ChatGroupController::class, 'createForm'], 'chat.groups.create');
$router->post('/chat/groups/create', [ChatGroupController::class, 'create'], 'chat.groups.create.post');
$router->get('/chat/groups/{slug}', [ChatGroupController::class, 'show'], 'chat.groups.show');
$router->post('/chat/groups/{slug}/join', [ChatGroupController::class, 'join'], 'chat.groups.join');
$router->post('/chat/groups/{slug}/leave', [ChatGroupController::class, 'leave'], 'chat.groups.leave');
$router->post('/chat/groups/{slug}/messages', [ChatGroupController::class, 'postMessage'], 'chat.groups.messages');
$router->post('/chat/groups/{slug}/messages/{messageId}/delete', [ChatGroupController::class, 'deleteMessage'], 'chat.groups.messages.delete');

// Direct messages (1:1)
$router->get('/chat/messages', [DirectMessageController::class, 'inbox'], 'chat.messages');
$router->get('/chat/messages/{username}', [DirectMessageController::class, 'thread'], 'chat.messages.thread');
$router->post('/chat/messages/send', [DirectMessageController::class, 'send'], 'chat.messages.send');

// Member social (feed, profiles, chat)
$router->get('/feed', [FeedController::class, 'index'], 'feed');
$router->get('/feed/more', [FeedController::class, 'more'], 'feed.more');
$router->post('/feed', [FeedController::class, 'store'], 'feed.post');
$router->get('/members/{username}', [MemberController::class, 'show'], 'members.show');
$router->get('/profile', [MemberController::class, 'index'], 'profile.index');
$router->get('/profile/edit', [MemberController::class, 'edit'], 'profile.edit');
$router->post('/profile/edit', [MemberController::class, 'update'], 'profile.update');
$router->get('/messages', [DirectMessageController::class, 'inbox'], 'messages');
$router->get('/messages/new', [DirectMessageController::class, 'startNew'], 'messages.new');
$router->post('/messages/contact', [DirectMessageController::class, 'contactPlayer'], 'messages.contact');
$router->get('/messages/{username}', [DirectMessageController::class, 'thread'], 'messages.thread');
$router->post('/messages/send', [DirectMessageController::class, 'send'], 'messages.send');

// Referrals (user invite + tracking)
$router->get('/referrals', [ReferralController::class, 'index'], 'referrals.index');
$router->get('/invite', [ReferralController::class, 'index'], 'referrals.invite');
$router->get('/r/{code}', [ReferralController::class, 'track'], 'referrals.track');

// Static pages
$router->get('/about', [PageController::class, 'about'], 'about');
$router->get('/contact', [PageController::class, 'contact'], 'contact');
$router->post('/contact', [PageController::class, 'submitContact'], 'contact.submit');
$router->get('/privacy', [PageController::class, 'privacy'], 'privacy');
$router->get('/terms', [PageController::class, 'terms'], 'terms');
$router->get('/data-deletion', [PageController::class, 'dataDeletion'], 'data_deletion');

// Auth
// KickOff Arcade — interactive mini-games
$router->get('/play', [PlayController::class, 'index'], 'play.index');
$router->get('/play/penalties', [PlayController::class, 'penaltiesRemoved'], 'play.penalties');

$router->get('/login', [AuthController::class, 'showLogin'], 'login');
$router->post('/login', [AuthController::class, 'login'], 'login.post');
$router->get('/register', [AuthController::class, 'showRegister'], 'register');
$router->post('/register', [AuthController::class, 'register'], 'register.post');
$router->post('/logout', [AuthController::class, 'logout'], 'logout');
$router->get('/forgot-password', [AuthController::class, 'showForgot'], 'password.request');
$router->post('/forgot-password', [AuthController::class, 'sendReset'], 'password.email');
$router->get('/reset-password/{token}', [AuthController::class, 'showReset'], 'password.reset');
$router->post('/reset-password', [AuthController::class, 'resetPassword'], 'password.update');

// Social OAuth (Google, Facebook, LinkedIn)
$router->get('/auth/google', [OAuthController::class, 'google'], 'auth.google');
$router->get('/auth/google/callback', [OAuthController::class, 'googleCallback'], 'auth.google.callback');
$router->get('/auth/facebook', [FacebookOAuthController::class, 'facebook'], 'auth.facebook');
$router->get('/auth/facebook/callback', [FacebookOAuthController::class, 'facebookCallback'], 'auth.facebook.callback');
$router->post('/auth/facebook/data-deletion', [FacebookOAuthController::class, 'dataDeletion'], 'auth.facebook.data_deletion');
$router->get('/auth/facebook/data-deletion/status', [FacebookOAuthController::class, 'dataDeletionStatus'], 'auth.facebook.data_deletion.status');
$router->get('/auth/linkedin', [OAuthController::class, 'linkedin'], 'auth.linkedin');
$router->get('/auth/linkedin/callback', [OAuthController::class, 'linkedinCallback'], 'auth.linkedin.callback');

// Admin
$router->get('/admin', [AdminController::class, 'dashboard'], 'admin.dashboard');
$router->get('/admin/users', [AdminController::class, 'users'], 'admin.users');
$router->post('/admin/users/role', [AdminController::class, 'updateUserRole'], 'admin.users.role');
$router->get('/admin/crm', [CrmController::class, 'dashboard'], 'admin.crm.dashboard');
$router->get('/admin/crm/contacts', [CrmController::class, 'contacts'], 'admin.crm.contacts');
$router->get('/admin/crm/contacts/export', [CrmController::class, 'exportContacts'], 'admin.crm.contacts.export');
$router->get('/admin/crm/contacts/new', [CrmController::class, 'createContactForm'], 'admin.crm.contacts.new');
$router->post('/admin/crm/contacts', [CrmController::class, 'storeContact'], 'admin.crm.contacts.store');
$router->get('/admin/crm/contacts/{id}', [CrmController::class, 'showContact'], 'admin.crm.contacts.show');
$router->get('/admin/crm/contacts/{id}/edit', [CrmController::class, 'editContactForm'], 'admin.crm.contacts.edit');
$router->post('/admin/crm/contacts/{id}', [CrmController::class, 'updateContact'], 'admin.crm.contacts.update');
$router->post('/admin/crm/contacts/{id}/activity', [CrmController::class, 'addActivity'], 'admin.crm.contacts.activity');
$router->get('/admin/crm/campaigns', [CrmController::class, 'campaigns'], 'admin.crm.campaigns');
$router->get('/admin/crm/campaigns/new', [CrmController::class, 'createCampaignForm'], 'admin.crm.campaigns.new');
$router->post('/admin/crm/campaigns', [CrmController::class, 'storeCampaign'], 'admin.crm.campaigns.store');
$router->get('/admin/crm/campaigns/{id}/edit', [CrmController::class, 'editCampaignForm'], 'admin.crm.campaigns.edit');
$router->post('/admin/crm/campaigns/{id}', [CrmController::class, 'updateCampaign'], 'admin.crm.campaigns.update');
$router->get('/admin/referrals', [ReferralController::class, 'adminReport'], 'admin.referrals');
$router->get('/admin/players', [AdminPlayerController::class, 'index'], 'admin.players');
$router->get('/admin/players/create', [AdminPlayerController::class, 'createForm'], 'admin.players.create');
$router->post('/admin/players', [AdminPlayerController::class, 'store'], 'admin.players.store');
$router->get('/admin/players/bulk', [AdminPlayerController::class, 'bulkForm'], 'admin.players.bulk');
$router->post('/admin/players/bulk', [AdminPlayerController::class, 'bulkStore'], 'admin.players.bulk.store');
$router->get('/admin/players/{id}/edit', [AdminPlayerController::class, 'editForm'], 'admin.players.edit');
$router->post('/admin/players/{id}', [AdminPlayerController::class, 'update'], 'admin.players.update');
$router->post('/admin/players/{id}/delete', [AdminPlayerController::class, 'destroy'], 'admin.players.delete');
$router->get('/admin/players/{id}/analysis', [PlayerAnalysisController::class, 'adminShow'], 'admin.players.analysis');
$router->post('/admin/players/{id}/analysis/regenerate', [PlayerAnalysisController::class, 'adminRegenerate'], 'admin.players.analysis.regenerate');
$router->post('/admin/players/{id}/health-records', [PlayerAnalysisController::class, 'adminStoreHealth'], 'admin.players.health.store');
$router->post('/admin/players/{id}/health', [PlayerAnalysisController::class, 'adminStoreHealth'], 'admin.players.health');
$router->get('/admin/talent-categories', [AdminTalentCategoryController::class, 'index'], 'admin.talent_categories');
$router->get('/admin/talent-categories/create', [AdminTalentCategoryController::class, 'createForm'], 'admin.talent_categories.create');
$router->post('/admin/talent-categories', [AdminTalentCategoryController::class, 'store'], 'admin.talent_categories.store');
$router->get('/admin/talent-categories/{id}/edit', [AdminTalentCategoryController::class, 'editForm'], 'admin.talent_categories.edit');
$router->post('/admin/talent-categories/{id}', [AdminTalentCategoryController::class, 'update'], 'admin.talent_categories.update');
$router->post('/admin/talent-categories/{id}/delete', [AdminTalentCategoryController::class, 'destroy'], 'admin.talent_categories.delete');
$router->get('/admin/discovery', [AdminDiscoveryController::class, 'index'], 'admin.discovery');
$router->post('/admin/discovery/toggle', [AdminDiscoveryController::class, 'toggle'], 'admin.discovery.toggle');
$router->post('/admin/discovery/weights', [AdminDiscoveryController::class, 'saveWeights'], 'admin.discovery.weights');
$router->get('/admin/social-auth', [AdminSocialAuthController::class, 'index'], 'admin.social_auth');
$router->post('/admin/social-auth', [AdminSocialAuthController::class, 'save'], 'admin.social_auth.save');
$router->get('/admin/{resource}', [AdminController::class, 'resource'], 'admin.resource');
