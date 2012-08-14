<script type="text/javascript">
	$(document).ready(function(){
		$(".page-title.bucket-title.cf div h1,.page-title.river-title.cf div h1").append($("#feed-icon-template").html());
		$("span.rss-feed").fadeIn()
	})
</script>

<style type="text/css">
  .rss-feed img {
    height: 12px;
  }
</style>

<script type="text/template" id="feed-icon-template">
<span class="rss-feed nodisplay">
	<a href="<?php echo $feed_url; ?>" target="_blank">
		<?php echo Html::image("themes/default/media/img/channel-rss.gif"); ?>
	</a>
</span>
</script>