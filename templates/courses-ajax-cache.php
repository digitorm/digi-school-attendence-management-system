<script>
	$j(function(){
		var tn = 'courses';

		/* data for selected record, or defaults if none is selected */
		var data = {
		};

		/* initialize or continue using AppDigi.cache for the current table */
		AppDigi.cache = AppDigi.cache || {};
		AppDigi.cache[tn] = AppDigi.cache[tn] || AppDigi.ajaxCache();
		var cache = AppDigi.cache[tn];

		cache.start();
	});
</script>

