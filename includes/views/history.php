<tr class="table-header">
	<td>#</td>
	<td></td>
	<td><i class="fa fa-music"></i>&nbsp; Title</td>
	<td><i class="fa fa-microphone"></i>&nbsp; Artist</td>
	<td><i class="fa fa-user"></i>&nbsp; Queued by</td>
	<td><i class="fa fa-clock-o"></i>&nbsp; Played on</td>
</tr>
<script src="js/list-view.js"></script>
<script>
	getStrimmerListJSON(0,0,"added","desc","history",function(){
		for(i=0;i<50;i++) {
			addStrimmerRow(i);
		}
	});
</script>
