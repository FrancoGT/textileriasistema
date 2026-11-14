window.onload = function () {
    che = document.getElementById("che").innerHTML;
    if(che!=null)
    {
        if(che==1000)
            document.getElementById("folder1").checked = 1;
        if(che==2000)
            document.getElementById("folder2").checked = 1;
        if(che==3000)
            document.getElementById("folder3").checked = 1;
        if(che>=4000 && che<=5999)
		{
            document.getElementById("folder4").checked = 1;
			if(che==4001)
				document.getElementById("subfolder41").checked = 1;
			if(che==4002)
				document.getElementById("subfolder42").checked = 1;
		}
        if(che>=6000 && che<=7999)
		{
            document.getElementById("folder5").checked = 1;
			if(che==6001)
				document.getElementById("subfolder51").checked = 1;
			if(che==6002)
				document.getElementById("subfolder52").checked = 1;
		}
        if(che==9000)
            document.getElementById("folder6").checked = 1;
    }
}

function desmarcar(id)
{
    document.getElementById("folder1").checked = 0;
    document.getElementById("folder2").checked = 0;
    document.getElementById("folder3").checked = 0;
    document.getElementById("folder4").checked = 0;
	document.getElementById("subfolder41").checked = 0;
	document.getElementById("subfolder42").checked = 0;
    document.getElementById("folder5").checked = 0;
	document.getElementById("subfolder51").checked = 0;
	document.getElementById("subfolder52").checked = 0;
    document.getElementById("folder6").checked = 0;
    marcar(id);
}

function marcar(id)
{
    document.getElementById(id).checked = 1;
}