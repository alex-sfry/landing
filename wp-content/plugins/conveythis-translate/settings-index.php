<?php require_once ('views/signup-modal.php'); ?>
<?php if (empty($this->api_key)): ?>
    <?php require_once ('views/login.php'); ?>
<?php else: ?>
    <div class="wrap">

        <?php require_once ('views/expired-message.php');?>
        <div class="settings-block">
            <form method="post" class="conveythis-widget-option-form" action="options.php" class="w-100">
                <?php settings_fields( 'my-plugin-settings-group' ); ?>
                <?php do_settings_sections( 'my-plugin-settings-group' ); ?>
                <div class="main-block">
                    <!--Head block-->
                    <div class="row justify-content-between w-100 align-items-center mx-auto">
                        <div class="col-md-2 text-center">
                            <div><a href="https://www.conveythis.com/" target="_blank"><img src="<?php echo CONVEY_PLUGIN_PATH;?>images/logo-convey.png" alt="ConveyThis"></a></div>
                        </div>
                    </div>
                    <!--Separator-->
                    <div class="line-grey"></div>

                    <div class="d-flex align-items-start w-100">
                        <ul class="nav nav-tabs mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="custom-pill nav-link active" id="main-tab" data-bs-toggle="pill" data-bs-target="#v-pills-main" type="button" role="tab" aria-controls="v-pills-main" aria-selected="true">Main configuration</button>
                            </li>
                            <?php if (isset($this->api_key) && !empty($this->api_key)): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="custom-pill nav-link" id="general-tab" data-bs-toggle="pill" data-bs-target="#v-pills-general" type="button" role="tab" aria-controls="v-pills-general" aria-selected="false">Extended settings</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="custom-pill nav-link" id="widget-style-tab" data-bs-toggle="pill" data-bs-target="#v-pills-widget" type="button" role="tab" aria-controls="v-pills-widget" aria-selected="false">Widget Style</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="custom-pill nav-link" id="block-pages-tab" data-bs-toggle="pill" data-bs-target="#v-pills-block" type="button" role="tab" aria-controls="v-pills-block" aria-selected="false">Block pages</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="custom-pill nav-link" id="glossary-tab" data-bs-toggle="pill" data-bs-target="#v-pills-glossary" type="button" role="tab" aria-controls="v-pills-glossary" aria-selected="false">Glossary</button>
                                </li>
                                <?php /* if($this->cacheTranslateSize > 0) { */?>
                                    <li class="nav-item" role = "presentation" >
                                        <button class="custom-pill nav-link" id="cache-tab" data-bs-toggle="pill"
                                                data-bs-target="#v-pills-cache" type="button" role="tab"
                                                aria-controls="v-pills-cache" aria-selected="false"> Cache
                                        </button>
                                </li>
                                <?php /* } */ ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <!--Tabs-->
                    <div class="tab-content" id="pills-tabContent">
                        <?php require_once ('views/main-configuration.php')?>
                        <?php require_once ('views/general-settings.php')?>
                        <?php require_once ('views/widget-style.php')?>
                        <?php require_once ('views/block-pages.php')?>
                        <?php require_once ('views/glossary.php')?>
                        <?php require_once ('views/cache.php')?>
                    </div>

                    <!--Separator-->
                    <div class="line-grey"></div>


                    <!--Submit button-->
                    <input type="submit" name="submit" id="submit" class="btn btn-primary btn-custom" value="Save Changes">
                </div>
            </form>
        </div>

        <div class="my-5" align="center" style="font-size: 14px">
            <a href="https://wordpress.org/support/plugin/conveythis-translate/reviews/#postform" target="_blank">Love ConveyThis? Give us 5 stars on WordPress.org</a><br>
            If you need any help, you can contact us via our live chat at <a href="https://www.conveythis.com/?utm_source=widget&utm_medium=wordpress" target="_blank">www.ConveyThis.com</a> or email us at support@conveythis.com. You can also check our <a href="https://www.conveythis.com/faqs/?utm_source=widget&utm_medium=wordpress" target="_blank">FAQ</a>
        </div>


    </div>
<?php endif ?>


<?php

wp_enqueue_style('conveythis-dropdown', plugins_url('css/dropdown.min.css', __FILE__) );
wp_enqueue_style('conveythis-input', plugins_url('css/input.min.css', __FILE__) );
wp_enqueue_style('conveythis-transition', plugins_url('css/transition.min.css',__FILE__) );
wp_enqueue_style('conveythis-range', plugins_url('css/range.css',__FILE__) );
wp_enqueue_style('conveythis-style', plugins_url('css/style.css?',__FILE__) );
wp_enqueue_style('conveythis-bootstrap-css', '//cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
wp_enqueue_style('conveythis-toastr', '//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css')


?>
<?php


wp_enqueue_script('conveythis-dropdown', plugins_url('js/dropdown.min.js',__FILE__), array(), null, true);
wp_enqueue_script('conveythis-toastr', '//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js');
wp_enqueue_script('conveythis-bootstrap-js', '//cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js');
wp_enqueue_script('conveythis-pusher', '//js.pusher.com/7.2/pusher.min.js');
wp_enqueue_script('conveythis-sweetalert', '//cdn.jsdelivr.net/npm/sweetalert2@11');
wp_enqueue_script('conveythis-transition', plugins_url('js/transition.min.js',__FILE__), array('jquery'), null, true);
wp_enqueue_script('conveythis-range', plugins_url('js/range.js',__FILE__), array('jquery'), null, true);
wp_enqueue_script('conveythis-plugin', CONVEYTHIS_JAVASCRIPT_PLUGIN_URL."/conveythis-preview.js", [], '6.3');

wp_enqueue_script('conveythis-settings', plugins_url('js/settings.js?v=12',__FILE__), array('jquery'), null, true);




?>
