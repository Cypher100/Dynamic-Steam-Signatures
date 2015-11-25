$(document).ready(function(){
	$("#ShowSig").hide();
	$("#Button01").click(function(event){
		$("#steamid").val($("#steamid").val().split("id/").pop().split("profiles/").pop().split("/")[0]);
		$('#ShowInfo').html('<img src="' + $(location).attr('href') + 'steam/images/' + $("#skin").val() + '/' + $("#steamid").val() + '.png"/><br />' + 'BBcode:<br /><textarea class="span8" rows="2" onclick="javascript:select();">[img]' + $(location).attr('href') + 'steam/images/' + $("#skin").val() + '/' + $("#steamid").val() + '.png[/img]</textarea><br />' + 'Direct:<br /><textarea class="span8" rows="2" onclick="javascript:select();">' + $(location).attr('href') + 'steam/images/' + $("#skin").val() + '/' + $("#steamid").val() + '.png</textarea><br />' + 'HTML:<br /><textarea class="span8" rows="2" onclick="javascript:select();"><img src="' + $(location).attr('href') + 'steam/images/' + $("#skin").val() + '/' + $("#steamid").val() + '.png" border="0" alt="' + $("#steamid").val() + '"/></textarea><br />');
		$("#SubmitBox").fadeOut("slow", function() {
		$("#ShowSig").fadeIn("slow");
		});
		event.preventDefault();
	});
	$("#Reset").click(function(event){
		$("#ShowSig").fadeOut("slow", function() {
		$("#SubmitBox").fadeIn("slow");
		});
		event.preventDefault();
	});
});