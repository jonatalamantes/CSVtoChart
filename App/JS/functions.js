function subirAdjunto(obj, evento)
{
    evento.preventDefault();
    var f = $(obj);

    var formData = new FormData(document.getElementById("formuploadajax"));
    formData.append("nombreArchivo", $("#nombreArchivo").html());

    $.ajax({
        url: "JS/scriptsAjax/subirAdjunto.php",
        type: "post",
        dataType: "html",
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    })
    .done(function(res)
    {           
        if (res.indexOf("KO") >= 0)
        {
            alert("No se pudo subir el archivo");
        }   
        else
        {
            $("#formuploadajax").css("display", "none");
            $("#mensajeSubida").css("display", "block");
            $("#archivoSubido").html("1");

            var string = "";
            var pieces = res.split("|");
            for (var i = 0; i < pieces.length; i++)
            {
                string += "<input type='checkbox' id='" + pieces[i] + "' class='attribs'>" + pieces[i];
            }

            $("#attrs").html(string);
        }
    })
    .fail(function(res)
    {
        alert("No se pudo subir el archivo");
    });    
}

function staticCalculation()
{
    $("#btnCalcular").attr("disabled", true);

    var targets = "";
    var auxTar  = $(".attribs:checked");

    for (var i = 0; i < auxTar.length; i++)
    {
        targets += auxTar[i].id + "|";
    }

    console.log(targets);
    targets = targets.substr(0, targets.length-1);

    var percent = "false";
    if ($("#percent:checked").length != 0)
    {
        percent = "true";
    }

    $.ajax
    ({
        data: 
            {
                'nombreArchivo' : $("#nombreArchivo").html(),
                'porcentaje'    : percent,
                'targets'       : targets
            },
        type: "POST",
        cache: false,
        url: 'JS/scriptsAjax/estadistica.php',
    })
    .done(function(res)
    {           
        $("#static").html(res);
        $("#btnCalcular").attr("disabled", false);
    })
    .fail(function(res)
    {
        alert("No se pudo subir el archivo");
        $("#btnCalcular").attr("disabled", false);
    });    
}
