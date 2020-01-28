<?php

namespace FSPoster\App\view;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;

defined('MODAL') or exit();
?>

<span class="close" data-modal-close="true">&times;</span>

<div style="margin: 10px;">
	<?php
	Helper::view('app_menus.posts');
	?>
</div>

<script>
	fsCode.modalWidth('<?=$mn?>' , '80');
</script>
