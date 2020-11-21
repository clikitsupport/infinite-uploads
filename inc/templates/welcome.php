<div class="card">
	<div class="card-body cloud p-5">
		<div class="row justify-content-center mb-5 mt-3">
			<div class="col text-center">
				<img class="mb-4" src="<?php echo esc_url( plugins_url( '/assets/img/iu-logo-blue.svg', dirname( __FILE__ ) ) ); ?>" alt="Push to Cloud" height="76" width="76"/>
				<h4><?php _e( 'Infinite Uploads Setup', 'iup' ); ?></h4>
				<p class="lead"><?php _e( "Welcome to Infinite Uploads, scalable cloud storage and delivery for your uploads made easy! Get started with a scan of your existing Media Library for smart recommendations choosing the best plan for your site, create or connect your account, and voilà – you're ready to push to the cloud.", 'iup' ); ?></p>
			</div>
		</div>
		<div class="row justify-content-center mb-5">
			<div class="col-2 text-center">
				<button class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#scan-modal"><?php _e( 'Run Scan', 'iup' ); ?></button>
			</div>
		</div>
		<div class="row justify-content-center mb-1">
			<div class="col-2 text-center">
				<img src="<?php echo esc_url( plugins_url( '/assets/img/progress-bar-0.svg', dirname( __FILE__ ) ) ); ?>" alt="Progress steps bar" height="19" width="110"/>
			</div>
		</div>
	</div>
</div>