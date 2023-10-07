<?php

namespace Kainex\WiseAnalytics\Reports\Pages;

class Overview {

	public function render() {
		$config = [
			'baseUrl' => site_url()
		];

		?>
			<script>var WA_API_BASE_URL = '<?php echo site_url().'/wp-json/wise-analytics/v1'; ?>';</script>
			<div class="waContainer" data-wa-config='<?php echo json_encode($config); ?>'></div>
		<?php
	}

}