<tr class="table-header">
	<td>#</td>
	<td></td>
	<td><i class="fa fa-music"></i>&nbsp; Title</td>
	<td><i class="fa fa-microphone"></i>&nbsp; Artist</td>
	<td><i class="fa fa-user"></i>&nbsp; Queued by</td>
	<td><i class="fa fa-clock-o"></i>&nbsp; Queued on</td>
</tr>
<script src="js/list-view.js"></script>
<script>
	getStrimmerListJSON(0,0,"added","asc","queue",function(){
		for(i=0;i<strimmer_data.RETURN_DATA.length;i++) {
			addStrimmerRow(i);
		}
	});
</script>
