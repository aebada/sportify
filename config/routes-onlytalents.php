<?php
/**
 * OnlyTalents subdomain routes — loaded when HTTP_HOST matches OT_HOST.
 * Same controllers & DB as main Sportify; separate URL paths at domain root.
 */

use App\Controllers\OnlyTalentsDiscoveryController;
use App\Controllers\OnlyTalentsController;
use App\Controllers\OtCommunityController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;

/** @var \App\Core\Router $router */

$router->get('/', [OnlyTalentsDiscoveryController::class, 'home'], 'ot.home');
$router->get('/discover', [OnlyTalentsDiscoveryController::class, 'discover'], 'ot.discover');
$router->get('/categories', [OnlyTalentsDiscoveryController::class, 'categories'], 'ot.categories');
$router->get('/categories/{slug}', [OnlyTalentsDiscoveryController::class, 'category'], 'ot.category');
$router->get('/locations', [OnlyTalentsDiscoveryController::class, 'locations'], 'ot.locations');
$router->get('/videos', [OnlyTalentsDiscoveryController::class, 'videos'], 'ot.videos');
$router->get('/profile/edit', [OnlyTalentsDiscoveryController::class, 'profileEdit'], 'ot.profile.edit');
$router->post('/profile/edit', [OnlyTalentsDiscoveryController::class, 'profileUpdate'], 'ot.profile.update');
$router->get('/profile/{slug}', [OnlyTalentsDiscoveryController::class, 'profile'], 'ot.profile');

$router->get('/api/shorts/load-more', [OnlyTalentsDiscoveryController::class, 'shortsLoadMore'], 'ot.shorts.load_more');
$router->post('/shorts/{id}/like', [OnlyTalentsDiscoveryController::class, 'shortsLike'], 'ot.shorts.like');
$router->get('/upload', [OnlyTalentsController::class, 'uploadForm'], 'only_talents.upload');
$router->post('/upload', [OnlyTalentsController::class, 'upload'], 'only_talents.upload.post');

$router->get('/communities', [OtCommunityController::class, 'index'], 'ot.communities');
$router->get('/communities/create', [OtCommunityController::class, 'createForm'], 'ot.communities.create');
$router->post('/communities', [OtCommunityController::class, 'store'], 'ot.communities.store');
$router->get('/my/communities', [OtCommunityController::class, 'myCommunities'], 'ot.communities.my');
$router->get('/communities/{slug}', [OtCommunityController::class, 'show'], 'ot.community.show');
$router->get('/communities/{slug}/join', [OtCommunityController::class, 'joinForm'], 'ot.community.join');
$router->post('/communities/{slug}/subscribe', [OtCommunityController::class, 'subscribe'], 'ot.community.subscribe');
$router->post('/communities/{slug}/posts', [OtCommunityController::class, 'storePost'], 'ot.community.post');
$router->post('/communities/{slug}/posts/{postId}/like', [OtCommunityController::class, 'likePost'], 'ot.community.like');
$router->get('/communities/{slug}/courses', [OtCommunityController::class, 'courses'], 'ot.community.courses');
$router->get('/communities/{slug}/courses/{courseSlug}', [OtCommunityController::class, 'courseShow'], 'ot.community.course');
$router->get('/communities/{slug}/courses/{courseSlug}/lessons/{id}', [OtCommunityController::class, 'lessonShow'], 'ot.community.lesson');
$router->get('/communities/{slug}/live', [OtCommunityController::class, 'live'], 'ot.community.live');
$router->get('/communities/{slug}/manage', [OtCommunityController::class, 'manage'], 'ot.community.manage');
$router->post('/communities/{slug}/courses', [OtCommunityController::class, 'storeCourse'], 'ot.community.course.store');
$router->post('/communities/{slug}/members/action', [OtCommunityController::class, 'memberAction'], 'ot.community.member');

$router->get('/switch-locale/{locale}', [HomeController::class, 'setLocale'], 'locale.switch');
$router->get('/lang/{locale}', [HomeController::class, 'setLocale'], 'lang.switch');

$router->get('/login', [AuthController::class, 'showLogin'], 'login');
$router->post('/login', [AuthController::class, 'login'], 'login.post');
$router->get('/register', [AuthController::class, 'showRegister'], 'register');
$router->post('/register', [AuthController::class, 'register'], 'register.post');
$router->post('/logout', [AuthController::class, 'logout'], 'logout');

$router->get('/only-talents/api/load-more', [OnlyTalentsController::class, 'loadMore'], 'only_talents.load_more');
$router->post('/only-talents/shorts/{id}/like', [OnlyTalentsController::class, 'like'], 'only_talents.like');
$router->get('/only-talents/upload', [OnlyTalentsController::class, 'uploadForm'], 'only_talents.upload');
$router->post('/only-talents/upload', [OnlyTalentsController::class, 'upload'], 'only_talents.upload.post');
