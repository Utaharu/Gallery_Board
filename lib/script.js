/* 
Gallery Board - JavaScript  
v2.0 19/06/24
*/

//UpFile Select Radio Control //
function valchange(id){
	obj = document.getElementById(id);
	
	obj.checked = true;
}

//UpFile FileSize //
function get_filesize(){
	if(document.getElementById('up_file')){
		var files = document.getElementById('up_file').files;
		if(files){
			var list = "";
			var unit = new Array('','K','M','G','T');
			
			for(var i = 0; i < files.length; i++){
				var file_size = files[i].size;
				var cnt = 0;
				do{
					file_size = file_size / 1024;
					cnt++;
				}while (file_size >= 1000);
				list += "(" + parseFloat(file_size).toFixed(2)  + " " + unit[cnt] +"Bytes)";
			}
			document.getElementById("UpFile_Size").innerHTML = list;
		}
	}
} 

//Auth //
function auth_ref(){
	if(document.getElementById('auth')){
		dd = new Date();
		document.getElementById('auth').src = php + '?mode=auth&stamp=' + dd.getTime();
	}
}

//Get Embed Code //
function Embed(obj,event){  
	url_match = obj.href.match(/^https?:\/\//i);
	
	if(url_match != null){
		event.preventDefault();//Event Cancel
		
		var Ajax = new XMLHttpRequest();

		Ajax.onreadystatechange = function() {
			if (Ajax.readyState === 4 && Ajax.status === 200) {
				response = Ajax.responseText;
				url_flag = response.match(/^(https?|ftp):\/\//i);
				if(url_flag == null){obj.innerHTML= response;}
			}
		}	
		
		Ajax.open('POST', php);
		Ajax.setRequestHeader('content-type', 'application/x-www-form-urlencoded;charset=' + encoding);
		Ajax.send( 'mode=Embed&url=' + obj.href);
		
		obj.onclick="";	
	}
}
