<form id="login-form" action="" method="post">
    <input type="hidden" name="set_api_key"  value="1"/>
    <div class="key-block mt-5">
        <div><a href="https://www.conveythis.com/" target="_blank"><img src="<?php echo CONVEY_PLUGIN_PATH;?>images/conveythis-logo-vertical-blue.png" alt="ConveyThis"></a></div>
        <div class="text">Take a few steps to setup plugin</div>
        <div class="row w-100 gap-20-sm align-items-center">
            <div class="col-md-6">
                <div class="step text-center">
                    <p><strong>Method 1. </strong>Complete a quick registration to get your API key</p>
                    <a href="#" class="btn btn-primary signup-modal">Get api key</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="step text-center">
                    <p><strong>Method 2. </strong>Login to <a href="//app.conveythis.com">ConveyThis</a> and paste api key here</p>
                    <div class="d-flex conveythis-input-text-api">
                        <input type="text" class="conveythis-input-text-api w-100" id="conveythis_api_key" name="api_key" value="" placeholder="pub_XXXXXXXXXXXXXXXXXXX" />
                        <button class="next btn-primary btn">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>