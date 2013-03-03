<html>
	<head>
		<title>Search Service</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js"></script>
		<script src="/qdmmmdb/lib/3rd_js/jstree-v.pre1.0/jquery.jstree.js"></script>
		<!-- /qdmmmdb/h/QDIndexer.index/ -->
		<script>
		$(document).on("ready",function () {
			$('#searchField').focus();
			$("#formSearch"	).submit(function(){
				$("#result"	).html('Searching ...');
				$.ajax({
					url			: "/qdmmmdb/p/QDIndexer.getFiles/",
					dataType	: "json",
					data		: {
						mode		: 'tree',
						search		: $('#searchField').val()
					},
					beforeSend	: function(){
					},
					complete	: function(data,result){
						var result = data.responseText;
						//result = result.replace(/"text"/g,'"data"');
						result = $.parseJSON(data.responseText);
						if(result && result.root && result.root.children && result.root.children[0]){
							$("#result").jstree({
								"json_data"	: {
									"data"		: result.root.children[0],
									"progressive_render" : true
								},
								core	: {
									"load_open"	: true
								},
								"themes" : {
									"theme" : "apple",
									"dots" : false,
									"icons" : false
								},
								"plugins" 	: [
									"themes",
									"json_data",
									"ui"
								]
							}).bind("select_node.jstree", function (e, data) {
								var txt = data.rslt.obj.attr('fullfilename');
								if(txt){
									alert(txt);
								}
							}).bind("loaded.jstree", function (event, data) {
								console.log(event,data);
							});
						}else{
							$("#result").html('no result');
						}
					}
				});

				return false;
			});
		});
		</script>
	</head>
	<body>
		<form id="formSearch">
			Search : <input type="text" value="" name="search" id="searchField"/><input type="submit">
		</fieldset>
		<div id="result"></div>
	</body>
</html>
