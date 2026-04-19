<?php

use App\Console\Commands\SyncCanvasAssets;
use App\Enums\Template\StatusEnum;
use App\Http\Controllers\Api\V1\User\ShippingAddress\ShippingController;
use App\Http\Controllers\Dashboard\{AdminController,
    BoardController,
    CategoryController,
    CreditOrderController,
    DiscountCodeController,
    FaqController,
    FixedSpecController,
    FlagController,
    FontController,
    IndustryController,
    InventoryController,
    InvoiceController,
    JobTicketController,
    LocationController,
    MessageController,
    MockupController,
    OfferController,
    OrderController,
    PermissionController,
    PlanController,
    ProductController,
    ProductSpecificationController,
    ProfileController,
    ReviewController,
    RoleController,
    SettingController,
    ShippingAddressController,
    StationStatusController,
    StatisticsController,
    SubCategoryController,
    SubIndustryController,
    TagController,
    TemplateController,
    UserController
};
use App\Models\Template;
use App\Services\Mockup\MockupRenderConfigResolver;
use App\Services\Mockup\MockupRenderModeResolver;
use App\Http\Controllers\Shared\{CommentController, LibraryAssetController};
use App\Http\Controllers\Shared\General\MainController;
use App\Http\Middleware\AutoCheckPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Mockup;
use App\Services\Mockup\MockupRenderer;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Services\Mockup\KittlSeatBucketRenderer;

Route::middleware(AutoCheckPermission::class)->group(function () {

    Route::view('/login/social', 'dashboard.auth.social-login');
    Route::view('confirm-password', 'dashboard.auth.confirm-password');
    Route::get('/reset-password', function (Request $request) {
        return view('dashboard.auth.reset-password', [
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    })->name('password.reset');
    Route::middleware('auth')->group(function () {
        Route::redirect('/', '/dashboard');
        Route::get('states', [MainController::class, 'states'])->name('states');
        Route::get('zones', [MainController::class, 'zones'])->name('zones');
        Route::post('industries/sub-industries', [IndustryController::class, 'getSubIndustries'])->name('sub-industries');

        Route::get('/dashboard', [StatisticsController::class, 'index'])->name('dashboard.index');
        Route::get('/dashboard/chart', [StatisticsController::class, 'chart'])
            ->name('dashboard.chart');

        Route::group(['prefix' => 'users', 'as' => 'users.', 'controller' => UserController::class,], function () {
            Route::get('/data', 'getData')->name('data');
            Route::get('/campaigns/data', 'getCampaignData')->name('campaigns.data');
            Route::post('/campaigns/send-sms', 'sendSms')->name('campaigns.send.sms');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
            Route::put('{id}/change-password', [UserController::class, 'changePassword'])->name('change-password');
            Route::get('/campaigns', [UserController::class, 'campaigns'])->name('campaigns');
            Route::get('/search', [UserController::class, 'search'])->name('search');
            Route::post('/{id}/extra-credits', [UserController::class, 'extraCredits'])->name('extra-credits');
        });
        Route::resource('/users', UserController::class);

        Route::prefix('/admins')->controller(AdminController::class)->as('admins.')->group(function () {
            Route::get('/data', 'getData')->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/admins', AdminController::class);

        Route::group(['prefix' => 'categories', 'as' => 'categories.', 'controller' => CategoryController::class,], function () {
            Route::get('/data', 'getData')->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
            Route::get('/search', 'search')->name('search');
            Route::post('/landing', 'addToLanding')->name('landing');
            Route::post('/landing/edit-category', 'editCategoryOnLanding')->name('landing.edit');
            Route::post('/landing/remove-category', 'removeFromLanding')->name('landing.remove');

        });
        Route::post('/without-categories', [CategoryController::class, 'storeProductWithoutCategories'])->name('product-without-categories.store');
        Route::put('/without-categories/{id}', [CategoryController::class, 'updateProductWithoutCategories'])->name('product-without-categories.update');

        Route::delete('categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('bulk-delete');

        Route::resource('/categories', CategoryController::class);

        Route::group(['prefix' => 'sub-categories', 'as' => 'sub-categories.', 'controller' => SubCategoryController::class,], function () {
            Route::get('/data', 'getData')->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/sub-categories', SubCategoryController::class);

        Route::group(['prefix' => 'products', 'as' => 'products.', 'controller' => ProductController::class,], (function () {
            Route::get('/data', [ProductController::class, 'getData'])->name('data');
            Route::get('/search', [ProductController::class, 'search'])->name('search');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
            Route::post('/categories', 'categories')->name('categories');

        }));
        Route::resource('/products', ProductController::class);

        Route::group(['prefix' => 'tags', 'as' => 'tags.', 'controller' => TagController::class,], (function () {
            Route::get('/data', [TagController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        }));
        Route::resource('/tags', TagController::class);

        Route::group(['prefix' => 'roles', 'as' => 'roles.', 'controller' => RoleController::class,], function () {
            Route::get('/data', [RoleController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/roles', RoleController::class)->except(['show']);

        Route::group(['prefix' => 'permissions', 'as' => 'permissions.', 'controller' => PermissionController::class,], function () {
            Route::get('/data', [PermissionController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');

        });
        Route::resource('/permissions', PermissionController::class)->except(['show']);

        Route::group(['prefix' => 'orders', 'as' => 'orders.', 'controller' => OrderController::class,], function () {
            Route::get('/data', [OrderController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
            Route::get('/pdf', 'downloadPDF')->name('pdf');
        });
        Route::resource('/orders', OrderController::class);

        Route::group(['prefix' => 'invoices', 'as' => 'invoices.', 'controller' => InvoiceController::class,], function () {
            Route::get('/data', [InvoiceController::class, 'getData'])->name('data');
            Route::get('/export', [InvoiceController::class, 'export'])->name('export');
            Route::get('/download/{id}', [InvoiceController::class, 'download'])->name('download');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/invoices', InvoiceController::class)->only(['show', 'destroy', 'index']);

        Route::group(['prefix' => 'faqs', 'as' => 'faqs.', 'controller' => FaqController::class,], function () {
            Route::get('/data', [FaqController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/faqs', FaqController::class)->except('show');

        Route::group(['prefix' => 'messages', 'as' => 'messages.', 'controller' => MessageController::class,], function () {
            Route::get('/data', [MessageController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
            Route::post('{id}/reply', 'reply')->name('reply');
        });
        Route::resource('/messages', MessageController::class)->except('show');

        Route::group(['prefix' => 'offers', 'as' => 'offers.', 'controller' => OfferController::class,], function () {
            Route::get('/data', [OfferController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/offers', OfferController::class)->except('show');

        Route::group(['prefix' => 'locations', 'as' => 'locations.', 'controller' => LocationController::class,], function () {
            Route::get('/data', 'getData')->name('data');
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/search', 'search')->name('search');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/locations', LocationController::class)->except('show');
        Route::get('/locations/dashboard', [LocationController::class, 'dashboard']);

        Route::group(['prefix' => 'discount-codes', 'as' => 'discount-codes.', 'controller' => DiscountCodeController::class,], function () {
            Route::get('/data', [DiscountCodeController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
            Route::post('/generate-export', 'generateAndExport')->name('generate.export');
            Route::post('/export', 'export')->name('export');
        });
        Route::resource('/discount-codes', DiscountCodeController::class)->except('show');

        Route::group(['prefix' => 'flags', 'as' => 'flags.', 'controller' => FlagController::class,], function () {
            Route::get('/data', [FlagController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/flags', FlagController::class);

        Route::group(['prefix' => 'station-statuses', 'as' => 'station-statuses.', 'controller' => StationStatusController::class,], function () {
            Route::get('/data', 'getData')->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/station-statuses', StationStatusController::class);

        Route::group(['prefix' => 'industries', 'as' => 'industries.', 'controller' => IndustryController::class,], function () {
            Route::get('/data', [IndustryController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/industries', IndustryController::class);

        Route::group(['prefix' => 'sub-industries', 'as' => 'sub-industries.', 'controller' => SubIndustryController::class,], function () {
            Route::get('/data', [SubIndustryController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/sub-industries', SubIndustryController::class);

        Route::group(['prefix' => 'templates', 'as' => 'templates.', 'controller' => TemplateController::class,], function () {


            Route::get('/data', [TemplateController::class, 'getData'])->name('data');
            Route::get('/search', 'search')->name('search');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
            Route::post('/landing', 'addToLanding')->name('landing');
            Route::post('/landing/remove-category', 'removeFromLanding')->name('landing.remove');
        });
        Route::post('/store-templates', [TemplateController::class, 'storeAndRedirect'])->name('templates.redirect.store');
        Route::put('/product-templates/{id}/change-status/{status}', [TemplateController::class, 'changeStatus'])
            ->whereIn('status', StatusEnum::values())
            ->name('product-templates.change-status.show');
        Route::post('/product-templates/import', [TemplateController::class, 'import'])
            ->name('product-templates.import');
        Route::put('/product-templates/{template}/best-seller',
            [TemplateController::class, 'toggleBestSeller'])
            ->name('product-templates.best-seller.toggle');
        Route::resource('/product-templates', TemplateController::class);

        Route::controller(ProfileController::class)->prefix('profile')->group(function () {
            Route::post('check-old-password', 'checkOldPassword')->name('check-old-password');
            Route::post('change-password', 'changePassword')->name('change-password');
        });
        Route::resource('profile', ProfileController::class)->only(['index', 'update']);

        Route::controller(SettingController::class)->prefix('settings')->group(function () {
            Route::get('/details', 'details')->name('settings-details.show');
            Route::post('/details', 'editDetails')->name('settings-edit-details');
            Route::get('/payments', 'payments')->name('settings-payments.show');
            Route::post('/payments/{payment}', 'togglePayments')->name('toggle-payment-methods');
            Route::get('/website', 'website')->name('settings-website.show');
            Route::get('/notifications', 'notifications')->name('settings-notifications.show');
            Route::post('notifications', 'updateNotifications')
                ->name('settings.notifications.update');
            Route::post('notifications/read/{notification?}', 'raedAllNotifications');

            Route::post('/carousels/{carousel?}', 'createOrUpdateCarousel')->name('carousels.update');

            Route::delete('carousels/{carousel}', 'removeCarousel')->name('carousels.remove');

            Route::put('landing-sections', 'landingSections')->name('landing-sections.update');

            Route::put('statistics', 'updateStatisticsSection')->name('statistics-section.update');

            Route::post('partners', 'uploadPartners')->name('partners.create');
            Route::delete('partners/{partner}', 'removePartner')->name('partners.remove');

            Route::delete('reviews/{review}', 'removeReview')->name('reviews.remove');
            Route::post('reviews-with-images', 'storeReviewsWithImages')->name('reviews-images.create');
            Route::post('reviews', 'storeReviews')->name('reviews.create');

        });

        Route::group(['prefix' => 'mockups', 'as' => 'mockups.', 'controller' => MockupController::class,], function () {
            Route::get('/data', 'getData')->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        });
        Route::resource('/mockups', MockupController::class);

        Route::apiResource('/products', ProductController::class)->only(['show', 'index']);


        Route::group(['prefix' => 'jobs', 'as' => 'job-tickets.', 'controller' => JobTicketController::class,], function () {
            Route::get('/data', 'getData')->name('data');
            Route::get('/{jobTicket}/pdf', 'pdf')->name('pdf');
        });
        Route::apiResource('/jobs', JobTicketController::class);

        Route::get('board', BoardController::class)->name('board.show');
        Route::view('/scan', 'dashboard.scan.kiosk')->name('scan.kiosk');
        Route::post('/scan', [JobTicketController::class, 'scan'])
            ->middleware('throttle:60,1')
            ->name('scan.submit');

        Route::group(['prefix' => 'inventories', 'as' => 'inventories.', 'controller' => InventoryController::class,], function () {
            Route::get('/data', 'getData')->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
            Route::get('/{parent}/available-places', 'availablePlaces')
                ->name('availablePlaces');
        });
        Route::resource('/inventories', InventoryController::class);

        Route::group(['prefix' => 'plans', 'as' => 'plans.', 'controller' => PlanController::class,], (function () {
            Route::get('/data', [PlanController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        }));
        Route::resource('/plans', PlanController::class);

        Route::group(['prefix' => 'credit-orders', 'as' => 'credit-orders.', 'controller' => CreditOrderController::class,], (function () {
            Route::get('/data', [CreditOrderController::class, 'getData'])->name('data');
            Route::post('/bulk-delete', [CreditOrderController::class, 'bulkDelete'])->name('bulk-delete');
        }));
        Route::resource('/credit-orders', CreditOrderController::class);


    });

    Route::prefix('api/v1/')->group(function () {
        Route::delete('fixed-specs/{product_specification}', [FixedSpecController::class, 'destroy'])
            ->name('fixed-specs.destroy');
        Route::controller(ReviewController::class)->group(function () {
            Route::delete('reviews/{review}', 'deleteReview')->name('reviews.destroy');
            Route::put('reviews/{review}/reply', 'deleteReply')->name('reviews.reply.destroy');
            Route::put('reviews/{review}', 'replyReview')->name('reviews.reply');
        });
        Route::put('templates/{template}/mockups/{mockup}/positions',[TemplateController::class,'savePositionsAndUploadMockups']);
        Route::put('templates/{template}/mockups/{mockup}/image',[TemplateController::class,'setTemplateImage']);
        Route::controller(MainController::class)->group(function () {
            Route::get('states', 'states')->name('states');
            Route::get('sub-categories', 'subCategories')->name('sub-categories');
            Route::get('station-statuses', 'stationStatuses')->name('station-statuses');
            Route::get('template-types', 'templateTypes')->name('template-types');
            Route::get('tags', 'tags')->name('tags');
            Route::get('units', 'units')->name('units');
            Route::delete('media/{media}', 'removeMedia')->name('remove-media');
            Route::post('media/{resource}', 'addMedia')->name('add-media');
            Route::post('dimensions', 'storeDimension')->name('dimensions.store');
            Route::post('get-dimensions', 'getDimensions')->name('dimensions.index');
            Route::get('admin-check', 'adminCheck')->name('admin-check');
        });

        Route::prefix("orders/")->controller(OrderController::class)->as("orders.")->group(function () {
            Route::post("step1", 'storeStep1')->name('step1');
            Route::post("step2", 'storeStep2')->name('step2');
            Route::post("template-customizations", 'templateCustomizations')->name('template.customizations');
            Route::post("apply-discount-code", 'applyDiscountCode')->name('apply-discount-code');
            Route::post("step4", 'storeStep4')->name('step4');
            Route::post("step5", 'storeStep5')->name('step5');
            Route::post("step6", 'storeStep6')->name('step6');
            Route::put('orders/{order}/edit-shipping-addresses', 'editShippingAddresses')->name('edit-shipping-addresses');
            Route::delete('orders/{orderId}/designs/{designId}', 'deleteDesign')->name('designs.delete');
            Route::get('/print/{order}', 'print')->name('printOrder');
            Route::get('/print', 'printNewOrders')->name('print');
        });
        Route::apiResource('templates', TemplateController::class)->only(['store', 'show', 'destroy']);
        Route::patch('templates/{template}', [TemplateController::class, 'updateEditorData']);
        Route::post('templates/{template}/library-assets', [TemplateController::class, 'attachMultipleLibraryAssets']);
        Route::get('templates/{template}/library-assets', [TemplateController::class, 'getLibraryAssets']);
        Route::post('templates/{template}/fonts', [TemplateController::class, 'attachMultipleFonts']);

        Route::get('templates', [TemplateController::class, 'getProductTemplates'])->name("templates.products");
        Route::get('template-assets', [TemplateController::class, 'templateAssets'])->name("templates.assets");
        Route::post('template-assets', [TemplateController::class, 'storeTemplateAssets'])->name("store.templates.assets");

        Route::apiResource('library-assets', LibraryAssetController::class)->only(['store', 'index']);
        Route::delete('fonts/styles/{font_style}', [FontController::class, 'destroyFontStyle']);
        Route::put('fonts/{font}/styles/{font_style}', [FontController::class, 'update']);
        Route::apiResource('fonts', FontController::class);
        Route::resource('shipping-addresses', ShippingAddressController::class)->only(['store', 'update', 'destroy']);

        Route::post('product-specifications', ProductSpecificationController::class)->name('products.specifications.create');
        Route::get('product-specifications/{product}', [ProductSpecificationController::class, 'getProductSpecs'])->name('products.specifications');

        Route::apiResource('comments', CommentController::class)->only(['store', 'index', 'destroy']);

        Route::controller(MockupController::class)->group(function () {
            // routes/web.php
            Route::post('mockups/remove-color', 'removeColor')
                ->name('mockups.remove-color');

            Route::get('mockups', 'index')->name('mockups.index');
            Route::get('mockups/{mockup}', 'showAndUpdateRecent');
            Route::get('mockups/{mockup}/side-settings/{side}', 'showAndUpdateRecent');
            Route::get('recent-mockups', 'recentMockups');
            Route::get('mockup-types', 'mockupTypes');
            Route::patch('mockups/{mockup}', 'updateEditorData');
            Route::delete('recent-mockups/{mockup}', 'destroyRecentMockup');
        });

        Route::post('check-product-type', [TemplateController::class, 'checkProductTypeInEditor']);
        Route::post('addMedia/{model_name?}/{model?}', [MainController::class, 'addMedia'])->name("media.store");
        Route::delete('/media/{media}', [MainController::class, 'removeMediaFromDashboard'])->name("media.destroy");
        Route::post('ship-blu/request-pickup', [ShippingController::class, 'requestPickup'])
            ->name('ship-blu.request-pickup');

        Route::post('social-links', [SettingController::class, 'socialLinks'])->name('social-links');
    });

});
Route::view('test', 'dashboard.test');
Route::get('test-canvas/{id}', function ($id){
    $template = Template::query()
        ->find($id);
    app(SyncCanvasAssets::class)->processCanvasColumn($template,'design_data');
    app(SyncCanvasAssets::class)->processCanvasColumn($template,'design_back_data');
});


Route::get('/debug/kittl-seat-bucket', function (Request $request, KittlSeatBucketRenderer $renderer) {
    abort_unless($request->query('token') === 'pixbyte-debug-123', 403);

    $assetsDir  = public_path('mockup-asset-test');
    $designsDir = $assetsDir . DIRECTORY_SEPARATOR . 'designs';
    $outputRoot = base_path();

    $mode = strtolower((string) $request->query('mode', 'logo'));
    $mode = in_array($mode, ['logo', 'full_art'], true) ? $mode : 'logo';

    $designFile = trim((string) $request->query('design', ''));
    $designPath = null;
    if ($designFile !== '') {
        $designPath = $designsDir . DIRECTORY_SEPARATOR . basename($designFile);
        abort_unless(is_file($designPath), 404, 'Design file not found.');
    }

    $hasCustomPolygon =
        $request->has('tlx') && $request->has('tly') &&
        $request->has('trx') && $request->has('try') &&
        $request->has('brx') && $request->has('bry') &&
        $request->has('blx') && $request->has('bly');

    $logoPolygon = null;
    if ($hasCustomPolygon) {
        $logoPolygon = [
            [(float) $request->query('tlx'), (float) $request->query('tly')],
            [(float) $request->query('trx'), (float) $request->query('try')],
            [(float) $request->query('brx'), (float) $request->query('bry')],
            [(float) $request->query('blx'), (float) $request->query('bly')],
        ];
    }

    $cfg = [
        'scene_path'     => $assetsDir . DIRECTORY_SEPARATOR . '1773934849445.scene.png',
        'dark_path'      => $assetsDir . DIRECTORY_SEPARATOR . '1773934849445.darkBlend.png',
        'light_path'     => $assetsDir . DIRECTORY_SEPARATOR . '1773934849445.lightBlend.png',

        'displacement_path'      => $assetsDir . DIRECTORY_SEPARATOR . '1773934849445.displacementMap.png',
        'front_panel_path'       => $assetsDir . DIRECTORY_SEPARATOR . '1773934849445.frontPanel.png',
        'highlight_path'         => $assetsDir . DIRECTORY_SEPARATOR . '1773934849445.highlightMap.png',

        'mode'           => $mode,
        'design_path'    => $designPath,
        'base_hex'       => (string) $request->query('base_hex', '#d9ff00'),
        'fit'            => (string) $request->query('fit', $mode === 'full_art' ? 'cover' : 'contain'),
        'design_scale'   => (float) $request->query('design_scale', 0.72),
        'offset_x_ratio' => (float) $request->query('offset_x_ratio', 0.0),
        'offset_y_ratio' => (float) $request->query('offset_y_ratio', -0.03),

        'dark_strength'  => (float) $request->query('dark_strength', $request->query('shadow_strength', 1.0)),
        'light_strength' => (float) $request->query('light_strength', $request->query('highlight_strength', 1.0)),

        'displacement_strength'  => (float) $request->query('displacement_strength', 14.0),
        'highlight_strength_map' => (float) $request->query('highlight_strength_map', 0.06),
        'use_front_panel'        => $request->boolean('use_front_panel', false),
        'front_panel_expand_px'  => (float) $request->query('front_panel_expand_px', 140),
        'design_opacity'         => (float) $request->query('design_opacity', 1.0),
        'alpha_blur'             => (float) $request->query('alpha_blur', 0.35),
        'logo_top_bias'          => (float) $request->query('logo_top_bias', 0.30),
        'clip_softness'          => (float) $request->query('clip_softness', 0.8),

        'trim_design'    => $request->boolean('trim_design', false),
        'trim_fuzz'      => (float) $request->query('trim_fuzz', 0.02),
        'src_x'          => (int) $request->query('src_x', 0),
        'src_y'          => (int) $request->query('src_y', 0),
        'src_w'          => (int) $request->query('src_w', 0),
        'src_h'          => (int) $request->query('src_h', 0),
    ];

    if ($logoPolygon) {
        $cfg['logo_polygon'] = $logoPolygon;
    }

    $debugStage = (string) $request->query('debug_stage', '');
    if ($debugStage !== '') {
        $cfg['debug_stage'] = $debugStage;
    }

    $png = $renderer->render($cfg);

    if ($request->boolean('save', false)) {
        $filename = ($debugStage ? $debugStage . '-' : 'render-') . date('Ymd-His') . '.png';
        $savePath = $outputRoot . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($savePath, $png);
    }

    return response($png, 200, [
        'Content-Type'  => 'image/png',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
});



Route::get('/debug/mockup-render-perspective', function (Request $request, MockupRenderer $renderer) {
    $token = (string) $request->query('token', '');
    abort_unless($token === 'pixbyte-debug-123', 403);

    $mockupId      = (int) $request->query('mockup_id');
    $designMediaId = (int) $request->query('design_media_id');
    $side          = strtolower((string) $request->query('side', 'front'));
    $hex           = $request->query('hex');
    $save          = $request->boolean('save', false);
    $debug         = $request->boolean('debug', false);
    $skipMaskClip  = $request->boolean('skip_mask_clip', false);

    $renderMode = strtolower((string) $request->query('render_mode', 'logo'));
    if (!in_array($renderMode, ['logo', 'full_art'], true)) {
        $renderMode = 'logo';
    }

    $presets = [
        'logo' => [
            'design_scale'       => 0.95,
            'texture_strength'   => 0.28,
            'highlight_strength' => 0.06,
            'shadow_strength'    => 1.03,
            'design_opacity'     => 0.96,
            'design_softness'    => 0.16,
            'displace_x'         => 1.0,
            'displace_y'         => 1.8,
            'displace_blur'      => 1.9,
            'displace_emboss'    => 0.28,
            'displace_contrast'  => 3.5,
        ],
        'full_art' => [
            // أكبر من 1 عشان يقدر يملأ المساحة
            'design_scale'       => 1.80,
            // أخف من logo mode للحفاظ على اللون
            'texture_strength'   => 0.18,
            'highlight_strength' => 0.04,
            'shadow_strength'    => 1.02,
            'design_opacity'     => 0.99,
            'design_softness'    => 0.08,
            'displace_x'         => 0.60,
            'displace_y'         => 1.10,
            'displace_blur'      => 2.30,
            'displace_emboss'    => 0.16,
            'displace_contrast'  => 2.0,
        ],
    ];

    $preset = $presets[$renderMode];

    $resolveFloat = function (string $key, float $default, float $min, float $max) use ($request): float {
        $value = $request->query($key, $default);
        $value = is_numeric($value) ? (float) $value : $default;
        return max($min, min($max, $value));
    };

    $designScale = $resolveFloat('design_scale', $preset['design_scale'], 0.05, 4.0);

    $textureStrength   = $resolveFloat('texture_strength', $preset['texture_strength'], 0.0, 1.0);
    $highlightStrength = $resolveFloat('highlight_strength', $preset['highlight_strength'], 0.0, 1.0);
    $shadowStrength    = $resolveFloat('shadow_strength', $preset['shadow_strength'], 0.0, 2.0);
    $designOpacity     = $resolveFloat('design_opacity', $preset['design_opacity'], 0.0, 1.0);
    $designSoftness    = $resolveFloat('design_softness', $preset['design_softness'], 0.0, 2.0);

    $displaceX        = $resolveFloat('displace_x', $preset['displace_x'], 0.0, 40.0);
    $displaceY        = $resolveFloat('displace_y', $preset['displace_y'], 0.0, 40.0);
    $displaceBlur     = $resolveFloat('displace_blur', $preset['displace_blur'], 0.0, 10.0);
    $displaceEmboss   = $resolveFloat('displace_emboss', $preset['displace_emboss'], 0.1, 10.0);
    $displaceContrast = $resolveFloat('displace_contrast', $preset['displace_contrast'], 0.0, 100.0);

    $mockup = Mockup::with(['media'])->findOrFail($mockupId);
    $designMedia = Media::findOrFail($designMediaId);

    $base = $mockup->getMedia('mockups')->first(fn ($m) =>
        $m->getCustomProperty('side') === $side &&
        $m->getCustomProperty('role') === 'base'
    );

    $mask = $mockup->getMedia('mockups')->first(fn ($m) =>
        $m->getCustomProperty('side') === $side &&
        $m->getCustomProperty('role') === 'mask'
    );

    $shadow = $mockup->getMedia('mockups')->first(fn ($m) =>
        $m->getCustomProperty('side') === $side &&
        $m->getCustomProperty('role') === 'shadow'
    );

    if (!$base || !$mask) {
        return response()->json([
            'ok' => false,
            'error' => 'base or mask missing',
        ], 422);
    }

    if (!file_exists($designMedia->getPath())) {
        return response()->json([
            'ok' => false,
            'error' => 'design missing',
            'design_path' => $designMedia->getPath(),
        ], 422);
    }

    $warpKeys = ['tlx', 'tly', 'trx', 'try', 'brx', 'bry', 'blx', 'bly'];

    $hasWarp = collect($warpKeys)->every(function ($key) use ($request) {
        $value = $request->query($key);
        return $value !== null && $value !== '' && is_numeric($value);
    });

    $warp = $hasWarp ? [
        'tl' => [(int) $request->query('tlx'), (int) $request->query('tly')],
        'tr' => [(int) $request->query('trx'), (int) $request->query('try')],
        'br' => [(int) $request->query('brx'), (int) $request->query('bry')],
        'bl' => [(int) $request->query('blx'), (int) $request->query('bly')],
    ] : null;

    if ($debug) {
        $baseImg = new \Imagick($base->getPath());
        $maskImg = new \Imagick($mask->getPath());

        $tmp = clone $maskImg;
        $tmp->trimImage(0);
        $page = $tmp->getImagePage();

        $maskBounds = [
            'x' => max(0, (int) ($page['x'] ?? 0)),
            'y' => max(0, (int) ($page['y'] ?? 0)),
            'w' => max(1, $tmp->getImageWidth()),
            'h' => max(1, $tmp->getImageHeight()),
        ];

        $tmp->clear();
        $tmp->destroy();
        $maskImg->clear();
        $maskImg->destroy();

        $baseSize = [
            'w' => $baseImg->getImageWidth(),
            'h' => $baseImg->getImageHeight(),
        ];

        $baseImg->clear();
        $baseImg->destroy();

        return response()->json([
            'ok' => true,
            'render_mode' => $renderMode,
            'mockup_id' => $mockupId,
            'design_media_id' => $designMediaId,
            'side' => $side,
            'hex' => $hex,
            'base' => $base->getPath(),
            'mask' => $mask->getPath(),
            'shadow' => $shadow?->getPath(),
            'design' => $designMedia->getPath(),
            'base_size' => $baseSize,
            'mask_bounds' => $maskBounds,
            'warp' => $warp,
            'design_scale' => $designScale,
            'texture_strength' => $textureStrength,
            'highlight_strength' => $highlightStrength,
            'shadow_strength' => $shadowStrength,
            'design_opacity' => $designOpacity,
            'design_softness' => $designSoftness,
            'displace_x' => $displaceX,
            'displace_y' => $displaceY,
            'displace_blur' => $displaceBlur,
            'displace_emboss' => $displaceEmboss,
            'displace_contrast' => $displaceContrast,
            'skip_mask_clip' => $skipMaskClip,
        ]);
    }

    $binary = $renderer->render([
        'base_path'         => $base->getPath(),
        'shirt_mask_path'   => $mask->getPath(),
        'shirt_shadow_path' => $shadow?->getPath(),
        'design_path'       => $designMedia->getPath(),
        'warp_points'       => $warp,
        'hex'               => $hex,
        'max_dim'           => 1600,
        'skip_mask_clip'    => $skipMaskClip,
        'render_mode'       => $renderMode,

        'design_scale'       => $designScale,
        'texture_strength'   => $textureStrength,
        'highlight_strength' => $highlightStrength,
        'shadow_strength'    => $shadowStrength,
        'design_opacity'     => $designOpacity,
        'design_softness'    => $designSoftness,
        'displace_x'         => $displaceX,
        'displace_y'         => $displaceY,
        'displace_blur'      => $displaceBlur,
        'displace_emboss'    => $displaceEmboss,
        'displace_contrast'  => $displaceContrast,
    ]);

    if ($save) {
        $dir = storage_path('app/debug');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $dir . "/mockup_perspective_{$mockupId}_{$designMediaId}_{$side}_{$renderMode}.png",
            $binary
        );
    }

    return response($binary, 200, [
        'Content-Type' => 'image/png',
    ]);
});


Route::get('/debug2/mockup-render-perspective', function (
    Request $request,
    MockupRenderer $renderer,
    MockupRenderConfigResolver $configResolver,
) {
    // ===== الحماية =====
    $token = (string) $request->query('token', '');
    abort_unless($token === 'pixbyte-debug-123', 403);

    // ===== المدخلات الأساسية =====
    $mockupId           = (int) $request->query('mockup_id');
    $designMediaId      = (int) $request->query('design_media_id');
    $side               = strtolower((string) $request->query('side', 'front'));
    $hex                = $request->query('hex');
    $save               = $request->boolean('save', false);
    $debug              = $request->boolean('debug', false);
    $skipMaskClip       = $request->boolean('skip_mask_clip', false);
    $renderModeOverride = $request->query('render_mode');

    // ===== جيب البيانات =====
    $mockup      = Mockup::with(['media', 'sideSettings'])->findOrFail($mockupId);
    $designMedia = Media::findOrFail($designMediaId);

    // ===== جيب الصور =====
    $mockupMedia = $mockup->getMedia('mockups');

    $base = $mockupMedia->first(fn($m) =>
        $m->getCustomProperty('side') === $side &&
        $m->getCustomProperty('role') === 'base'
    );

    $mask = $mockupMedia->first(fn($m) =>
        $m->getCustomProperty('side') === $side &&
        $m->getCustomProperty('role') === 'mask'
    );

    $shadow = $mockupMedia->first(fn($m) =>
        $m->getCustomProperty('side') === $side &&
        $m->getCustomProperty('role') === 'shadow'
    );

    if (!$base || !$mask) {
        return response()->json([
            'ok'    => false,
            'error' => 'base or mask missing',
            'side'  => $side,
        ], 422);
    }

    if (!file_exists($designMedia->getPath())) {
        return response()->json([
            'ok'          => false,
            'error'       => 'design file missing on disk',
            'design_path' => $designMedia->getPath(),
        ], 422);
    }

    // ===== احسب mask bounds =====
    $maskImg = new \Imagick($mask->getPath());
    $tmp     = clone $maskImg;
    $tmp->trimImage(0);
    $page    = $tmp->getImagePage();

    $maskBounds = [
        'x' => max(0, (int) ($page['x'] ?? 0)),
        'y' => max(0, (int) ($page['y'] ?? 0)),
        'w' => max(1, $tmp->getImageWidth()),
        'h' => max(1, $tmp->getImageHeight()),
    ];

    $tmp->clear();     $tmp->destroy();
    $maskImg->clear(); $maskImg->destroy();

    // ===== احسب design context لـ mode resolver =====
    $designImg  = new \Imagick($designMedia->getPath());
    $designW    = $designImg->getImageWidth();
    $designH    = $designImg->getImageHeight();
    $hasAlpha   = $designImg->getImageAlphaChannel() !== 0;
    $designImg->clear();
    $designImg->destroy();

    $designMime        = $designMedia->mime_type ?? 'image/png';
    $placedWidthRatio  = $designW / max(1, $maskBounds['w']);
    $placedHeightRatio = $designH / max(1, $maskBounds['h']);
    $coverageRatio     = ($placedWidthRatio + $placedHeightRatio) / 2;

    // ===== حدد الوضع تلقائياً =====
    $modeResolver   = app(MockupRenderModeResolver::class);
    $autoRenderMode = $modeResolver->resolve([
        'coverage_ratio'      => $coverageRatio,
        'placed_width_ratio'  => $placedWidthRatio,
        'placed_height_ratio' => $placedHeightRatio,
        'has_alpha'           => $hasAlpha,
        'mime'                => $designMime,
    ]);
    $finalRenderMode = $renderModeOverride ?? $autoRenderMode;

    // ===== جيب الإعدادات من الداتابيز =====
    $urlOverrides = collectUrlOverrides($request);

    $config     = $configResolver->resolve(
        mockup:     $mockup,
        side:       $side,
        renderMode: $finalRenderMode,
        overrides:  $urlOverrides,
    );

    $renderMode = $config['render_mode'];
    $preset     = $config['preset'];
    $warp       = resolveUrlWarp($request) ?? $config['warp_points'];

    // ===== وضع التشخيص =====
    if ($debug) {
        $baseImg  = new \Imagick($base->getPath());
        $baseSize = [
            'w' => $baseImg->getImageWidth(),
            'h' => $baseImg->getImageHeight(),
        ];
        $baseImg->clear();
        $baseImg->destroy();

        $sideSetting = $mockup->sideSettings->firstWhere('side', $side);

        return response()->json([
            'ok'               => true,
            'source'           => $sideSetting ? 'database' : 'defaults',
            'auto_render_mode' => $autoRenderMode,
            'final_render_mode'=> $finalRenderMode,
            'render_mode'      => $renderMode,
            'mockup_id'        => $mockupId,
            'side'             => $side,
            'hex'              => $hex,
            'warp'             => $warp,
            'preset'           => $preset,
            'url_overrides'    => $urlOverrides,
            'base_size'        => $baseSize,
            'mask_bounds'      => $maskBounds,
            'coverage_context' => [
                'coverage_ratio'      => $coverageRatio,
                'placed_width_ratio'  => $placedWidthRatio,
                'placed_height_ratio' => $placedHeightRatio,
                'has_alpha'           => $hasAlpha,
                'mime'                => $designMime,
            ],
            'paths' => [
                'base'   => $base->getPath(),
                'mask'   => $mask->getPath(),
                'shadow' => $shadow?->getPath(),
                'design' => $designMedia->getPath(),
            ],
            'side_setting'   => $sideSetting,
            'skip_mask_clip' => $skipMaskClip,
        ]);
    }

    // ===== الرندر الفعلي =====
    $binary = $renderer->render([
        'base_path'         => $base->getPath(),
        'shirt_mask_path'   => $mask->getPath(),
        'shirt_shadow_path' => $shadow?->getPath(),
        'design_path'       => $designMedia->getPath(),
        'hex'               => $hex,
        'warp_points'       => $warp,
        'max_dim'           => 1600,
        'skip_mask_clip'    => $skipMaskClip,
        'render_mode'       => $renderMode,
        ...$preset,
    ]);

    // ===== حفظ اختياري =====
    if ($save) {
        $dir = storage_path('app/debug');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = "mockup_{$mockupId}_{$designMediaId}_{$side}_{$renderMode}_" . now()->format('His') . ".png";
        file_put_contents("{$dir}/{$filename}", $binary);
    }

    return response($binary, 200, ['Content-Type' => 'image/png']);
});

Route::get('/debug3/mockup-render-assets', function (Request $request, MockupRenderer $renderer) {
    $token = (string) $request->query('token', '');
    abort_unless($token === 'pixbyte-debug-123', 403);

    $assetDir  = public_path('mockup-asset-test');
    $designDir = $assetDir . DIRECTORY_SEPARATOR . 'designs';

    $basePath         = $assetDir . DIRECTORY_SEPARATOR . 'base.png';
    $maskPath         = $assetDir . DIRECTORY_SEPARATOR . 'mask.png';
    $shadowPath       = $assetDir . DIRECTORY_SEPARATOR . 'shadow.png';
    $highlightPath    = $assetDir . DIRECTORY_SEPARATOR . 'highlight.png';
    $displacementPath = $assetDir . DIRECTORY_SEPARATOR . 'displacement_gray.png';

    $designFile = basename((string) $request->query('design', 'logo-text.png'));
    $designPath = $designDir . DIRECTORY_SEPARATOR . $designFile;

    if (!file_exists($basePath) || !file_exists($maskPath)) {
        return response()->json([
            'ok' => false,
            'error' => 'base or mask missing from public/mockup-asset-test',
            'paths' => [
                'base' => $basePath,
                'mask' => $maskPath,
            ],
        ], 422);
    }

    if (!file_exists($designPath)) {
        return response()->json([
            'ok' => false,
            'error' => 'design missing',
            'design' => $designPath,
        ], 422);
    }

    $side         = strtolower((string) $request->query('side', 'front'));
    $hex          = $request->query('hex');
    $save         = $request->boolean('save', false);
    $debug        = $request->boolean('debug', false);
    $skipMaskClip = $request->boolean('skip_mask_clip', false);

    $renderMode = strtolower((string) $request->query('render_mode', 'logo'));
    if (!in_array($renderMode, ['logo', 'full_art'], true)) {
        $renderMode = 'logo';
    }

    $presets = [
        'logo' => [
            'design_scale'          => 0.95,
            'texture_strength'      => 0.0,
            'highlight_strength'    => 0.0,
            'highlight_pass_opacity'=> 0.0,
            'shadow_strength'       => 0.45,
            'design_opacity'        => 1.0,
            'design_softness'       => 0.03,
            'displace_x'            => 5.0,
            'displace_y'            => 8.0,
            'displace_blur'         => 1.9,
            'displace_emboss'       => 0.28,
            'displace_contrast'     => 3.5,
        ],
        'full_art' => [
            'design_scale'          => 1.45,
            'texture_strength'      => 0.0,
            'highlight_strength'    => 0.0,
            'highlight_pass_opacity'=> 0.0,
            'shadow_strength'       => 0.30,
            'design_opacity'        => 1.0,
            'design_softness'       => 0.02,
            'displace_x'            => 4.0,
            'displace_y'            => 7.0,
            'displace_blur'         => 1.9,
            'displace_emboss'       => 0.28,
            'displace_contrast'     => 3.5,
        ],
    ];

    $preset = $presets[$renderMode];

    $resolveFloat = function (string $key, float $default, float $min, float $max) use ($request): float {
        $value = $request->query($key, $default);
        $value = is_numeric($value) ? (float) $value : $default;
        return max($min, min($max, $value));
    };

    $designScale          = $resolveFloat('design_scale', $preset['design_scale'], 0.05, 4.0);
    $textureStrength      = $resolveFloat('texture_strength', $preset['texture_strength'], 0.0, 1.0);
    $highlightStrength    = $resolveFloat('highlight_strength', $preset['highlight_strength'], 0.0, 1.0);
    $highlightPassOpacity = $resolveFloat('highlight_pass_opacity', $preset['highlight_pass_opacity'], 0.0, 1.0);
    $shadowStrength       = $resolveFloat('shadow_strength', $preset['shadow_strength'], 0.0, 2.0);
    $designOpacity        = $resolveFloat('design_opacity', $preset['design_opacity'], 0.0, 1.0);
    $designSoftness       = $resolveFloat('design_softness', $preset['design_softness'], 0.0, 2.0);
    $displaceX            = $resolveFloat('displace_x', $preset['displace_x'], 0.0, 40.0);
    $displaceY            = $resolveFloat('displace_y', $preset['displace_y'], 0.0, 40.0);
    $displaceBlur         = $resolveFloat('displace_blur', $preset['displace_blur'], 0.0, 10.0);
    $displaceEmboss       = $resolveFloat('displace_emboss', $preset['displace_emboss'], 0.1, 10.0);
    $displaceContrast     = $resolveFloat('displace_contrast', $preset['displace_contrast'], 0.0, 100.0);
    $maxDim               = (int) $request->query('max_dim', 1600);

    $warpKeys = ['tlx', 'tly', 'trx', 'try', 'brx', 'bry', 'blx', 'bly'];
    $placeXRatio = (float) $request->query('place_x_ratio', $renderMode === 'logo' ? 0.18 : 0.10);
    $placeYRatio = (float) $request->query('place_y_ratio', $renderMode === 'logo' ? 0.18 : 0.06);
    $placeWRatio = (float) $request->query('place_w_ratio', $renderMode === 'logo' ? 0.64 : 0.80);
    $placeHRatio = (float) $request->query('place_h_ratio', $renderMode === 'logo' ? 0.22 : 0.86);
    $placeFit    = (string) $request->query('place_fit', $renderMode === 'logo' ? 'contain' : 'cover');

    $hasWarp = collect($warpKeys)->every(function ($key) use ($request) {
        $value = $request->query($key);
        return $value !== null && $value !== '' && is_numeric($value);
    });

    $warp = $hasWarp ? [
        'tl' => ['x' => (int) $request->query('tlx'), 'y' => (int) $request->query('tly')],
        'tr' => ['x' => (int) $request->query('trx'), 'y' => (int) $request->query('try')],
        'br' => ['x' => (int) $request->query('brx'), 'y' => (int) $request->query('bry')],
        'bl' => ['x' => (int) $request->query('blx'), 'y' => (int) $request->query('bly')],
    ] : null;

    if ($debug) {
        $baseImg = new \Imagick($basePath);
        $maskImg = new \Imagick($maskPath);

        $tmp = clone $maskImg;
        $tmp->trimImage(0);
        $page = $tmp->getImagePage();

        $maskBounds = [
            'x' => max(0, (int) ($page['x'] ?? 0)),
            'y' => max(0, (int) ($page['y'] ?? 0)),
            'w' => max(1, $tmp->getImageWidth()),
            'h' => max(1, $tmp->getImageHeight()),
        ];

        $tmp->clear();
        $tmp->destroy();
        $maskImg->clear();
        $maskImg->destroy();

        $baseSize = [
            'w' => $baseImg->getImageWidth(),
            'h' => $baseImg->getImageHeight(),
        ];

        $baseImg->clear();
        $baseImg->destroy();

        return response()->json([
            'ok' => true,
            'side' => $side,
            'render_mode' => $renderMode,
            'design' => $designFile,
            'paths' => [
                'base' => $basePath,
                'mask' => $maskPath,
                'shadow' => file_exists($shadowPath) ? $shadowPath : null,
                'highlight' => file_exists($highlightPath) ? $highlightPath : null,
                'displacement' => file_exists($displacementPath) ? $displacementPath : null,
                'design' => $designPath,
            ],
            'base_size' => $baseSize,
            'mask_bounds' => $maskBounds,
            'warp' => $warp,
            'hex' => $hex,
            'design_scale' => $designScale,
            'texture_strength' => $textureStrength,
            'highlight_strength' => $highlightStrength,
            'highlight_pass_opacity' => $highlightPassOpacity,
            'shadow_strength' => $shadowStrength,
            'design_opacity' => $designOpacity,
            'design_softness' => $designSoftness,
            'displace_x' => $displaceX,
            'displace_y' => $displaceY,
            'displace_blur' => $displaceBlur,
            'displace_emboss' => $displaceEmboss,
            'displace_contrast' => $displaceContrast,
            'max_dim' => $maxDim,
            'skip_mask_clip' => $skipMaskClip,
        ]);
    }

    $binary = $renderer->render([
        'base_path'              => $basePath,
        'shirt_mask_path'        => $maskPath,
        'shirt_shadow_path'      => file_exists($shadowPath) ? $shadowPath : null,
        'shirt_highlight_path'   => file_exists($highlightPath) ? $highlightPath : null,
        'displacement_map_path'  => file_exists($displacementPath) ? $displacementPath : null,
        'design_path'            => $designPath,
        'warp_points'            => $warp,
        'hex'                    => $hex,
        'max_dim'                => $maxDim,
        'skip_mask_clip'         => $skipMaskClip,
        'render_mode'            => $renderMode,
        'design_scale'           => $designScale,
        'texture_strength'       => $textureStrength,
        'highlight_strength'     => $highlightStrength,
        'highlight_pass_opacity' => $highlightPassOpacity,
        'shadow_strength'        => $shadowStrength,
        'design_opacity'         => $designOpacity,
        'design_softness'        => $designSoftness,
        'displace_x'             => $displaceX,
        'displace_y'             => $displaceY,
        'displace_blur'          => $displaceBlur,
        'displace_emboss'        => $displaceEmboss,
        'displace_contrast'      => $displaceContrast,
        'place_x_ratio' => $placeXRatio,
        'place_y_ratio' => $placeYRatio,
        'place_w_ratio' => $placeWRatio,
        'place_h_ratio' => $placeHRatio,
        'place_fit'     => $placeFit,
    ]);

    if ($save) {
        $dir = storage_path('app/debug');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $safeDesign = preg_replace('/[^a-zA-Z0-9._-]/', '_', $designFile);
        file_put_contents(
            $dir . "/mockup_assets_{$side}_{$renderMode}_{$safeDesign}.png",
            $binary
        );
    }

    return response($binary, 200, [
        'Content-Type' => 'image/png',
    ]);
});
// ===== Helper Functions =====

function collectUrlOverrides(Request $request): array
{
    $overrides = [];

    $limits = [
        'design_scale'       => [0.05, 4.0],
        'texture_strength'   => [0.0,  1.0],
        'highlight_strength' => [0.0,  1.0],
        'shadow_strength'    => [0.0,  2.0],
        'design_opacity'     => [0.0,  1.0],
        'design_softness'    => [0.0,  2.0],
        'displace_x'         => [0.0,  40.0],
        'displace_y'         => [0.0,  40.0],
        'displace_blur'      => [0.0,  10.0],
        'displace_emboss'    => [0.1,  10.0],
        'displace_contrast'  => [0.0,  100.0],
    ];

    foreach ($limits as $key => [$min, $max]) {
        if ($request->has($key) && is_numeric($request->query($key))) {
            $overrides[$key] = max($min, min($max, (float) $request->query($key)));
        }
    }

    return $overrides;
}

function resolveUrlWarp(Request $request): ?array
{
    $keys   = ['tlx', 'tly', 'trx', 'try', 'brx', 'bry', 'blx', 'bly'];
    $hasAll = collect($keys)->every(fn($k) =>
        $request->query($k) !== null && is_numeric($request->query($k))
    );

    if (!$hasAll) return null;

    return [
        'tl' => ['x' => (int) $request->query('tlx'), 'y' => (int) $request->query('tly')],
        'tr' => ['x' => (int) $request->query('trx'), 'y' => (int) $request->query('try')],
        'br' => ['x' => (int) $request->query('brx'), 'y' => (int) $request->query('bry')],
        'bl' => ['x' => (int) $request->query('blx'), 'y' => (int) $request->query('bly')],
    ];
}
