<!doctype html>
<html lang="en">
	<head>
		<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
		<title>Dynamic Steam Signatures</title>
		<link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css"/>
		<link rel="stylesheet" type="text/css" href="./css/main.css"/>
		<!--[if IE]>
		<style>
			#form, #header, #ad {
				background-color: #121212;
			}
		</style>
		<center>
		<![endif]-->
	</head>
	<body>
		<script type="text/javascript" src="./js/jquert-1.5.min.js"></script>
		<script type="text/javascript" src="./js/mysteam.js"></script>
		<div id="header"><a href="./">Home</a></div>
                <div id="header">Enter your steam community name below and hit submit to generate your dynamic steam signature.</div>
		<div id="form">
				<div id="SubmitBox">
				Where to find CustomURL Name
				<br/>
				https://www.steamcommunity.com/id/<b>YourUrlName</b>
				<br/><br/>
				<form id="sform">
					CustomURL Name: 
					<input type="text" id="steamid" name="steamid"/>
					<br/><br/>
                                        Skin: 
                                        <select id="skin" name="skin">
                                            <?php include('skins.php');foreach($publicskins as $skin):?>
                                            <option value="<?php echo$skin[1]?>"><?php echo$skin[0]?></option>
                                            <?php endforeach?>
                                            <?php if($_GET["skincode"]==$skincode){?><?php include('skins.php');foreach($privateskins as $skin):?>
                                            <option value="<?php echo$skin[1]?>">## <?php echo$skin[0]?> ##</option>
                                             <?php endforeach?><?php };?>
					</select>
					<br/>
					<br/>
					<input id="Button01" class="btn primary" type="submit" value="Submit"/>
				</form>
				<b>Demo</b>
				<br/>
				<img type="image/png" src="./steam/images/default/keenen.png" border="0" alt="keenen"/>
				</a>
				</div>
				<div id="ShowSig">
					<a id="Reset" class="btn danger" href="#">Go Back</a><br/><br/>
					<div id="ShowInfo"></div>
				</div>
			</div>
			<br />
			<div style="font-size:.85em;text-align:center;width:615px;margin:0 auto;"><a href="https://github.com/Cypher100/Dynamic-Steam-Signatures/">Source Code</a><br/><br/></div>
	</body>
</html>