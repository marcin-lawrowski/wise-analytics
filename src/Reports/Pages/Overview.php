<?php

namespace Kainex\WiseAnalytics\Reports\Pages;

class Overview {

	public function render() {
		?>
			<script>var WA_API_BASE_URL = '<?php echo site_url().'/wp-json/wise-analytics/v1'; ?>';</script>
			<div class="waContainer" data-wa-config="{}"></div>
		<?php
	}

}