/* V1.3 */
function debug(txt)
{
	if(!txt) return false;
	if (typeof debug_mode=='undefined') return false;
	if (!debug_mode) return false;
	if (!document.getElementById("dump_log"))
	{
		$('head').append('<style type="text/css">#dump_log{position: fixed !important;bottom:0px;left:0px;right:0px;background-color:#f7fafb;z-index:1001;}#dump_log_content{overflow-y:scroll;overflow-x:hidden;height:380px;width:100%;position:relative;border:1px solid #77a6b7;margin-top:-20px;}.dump_log_head{background-color:#77a6b7;height:22px;line-height:22px;text-align: center;color:#ffffFF;width:100%;}.dump_log_close{background-color:#5892a7;height:21px;line-height:19px;width:20px;float:left;text-align: center;cursor:pointer;border:1px solid #5892a7;}.dump_log_clear{background-color:#5892a7;height:21px;line-height:19px;margin:1px;padding-left:2px;padding-right:2px;float:left;text-align: center;cursor:pointer;border:1px solid #5892a7;}#debug{display:none;}.debug_hr{padding:0px;margin:0px;border: 0;border-top: 1px solid #5892a7;}</style>');
		$( "body" ).append('<div id="dump_log"><div class="dump_log_head">..:: debug ::.. <div class="dump_log_close dump_log_close_js">X</div><div class="dump_log_clear dump_log_clear_js">clear</div></div><br clear="all"/><div id="dump_log_content"></div></div>');
		$('.dump_log_close_js').bind('click',function() 
		{
			$('#dump_log').fadeOut(250);
		});
		$('.dump_log_clear_js').bind('click',function() 
		{
			$('#dump_log_content').html('');
		});
	}
	var date = new Date;
	var time =  date.getFullYear()+'-'+date.getMonth()+'-'+date.getDate()+'  '+date.getHours()+':'+date.getMinutes()+':'+date.getSeconds();

	if (typeof txt === 'object' || typeof txt === 'array') txt= "<xmp>"+JSON.stringify(txt, null, 2)+"</xmp>";
	$("#dump_log_content" ).append('<div><div class="dump_log_head"><div class="dump_log_close dump_log_close_js_row">X</div><div style="float:left">..::'+time+'::..</div></div><div>'+unescape(txt)+"</div></div>");
	$('#dump_log').show();
	$('#dump_log_content').scrollTop($('#dump_log_content')[0].scrollHeight);
	$('.dump_log_close_js_row').unbind().bind('click',function() 
	{
		$(this).parent().parent().html('');
	});
}