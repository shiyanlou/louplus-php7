var userid = $("#noti-num").attr('data-id');
Pusher.logToConsole = true;
var pusher = new Pusher('44fef9776654257be723', {
      cluster: 'ap2',
      encrypted: true
});
var channel = pusher.subscribe("user."+userid);
channel.bind('moka', function(data) {
    var num = $("#noti-num").text();
    $("#noti-num").text(++num);
});
//edit
$("#edit-answer").click(function(){
    $("#answer").text($("#answer-content").val());
    $(".card-answer").show();
});

//comment
$("#btn-comment").click(function(){
    var ob = $(this).next('ul');
    if (ob) {
        ob.remove();
    } else {

    var li = "<li class='list-group-item'>Cras justo odio</li><li class='list-group-item'>Dapibus ac facilisis in</li><li class='list-group-item'>Vestibulum at eros</li>";
    var input = "<li class='list-group-item'><form class='form-inline'><div class='form-group'><input type='text' class='form-control' id='exampleInputEmail1' aria-describedby='emailHelp' placeholder='Enter email'></div><button type='submit' class='btn btn-primary'>Submit</button></form></li>";
    $(this).after("<ul class='list-group list-group-flush'>"+ li + input + "</ul>");
    }
})
//reply
$(".btn-reply").click(function(){
    var userid = $(this).attr('data-id');
    var username = $(this).attr('data-name');
    var form = $(".card-comment").find("form")
    form.find("#comment").attr("placeholder","回复"+username).focus();
    form.find("input#setid").val(userid);
})


//上传头像
$("#upload-img").click(function(){
    $("#upload-input").click().change(function(){
       var form = new FormData($("#upload-form")[0]);
       $.ajax({
           url: '/user/avatar',
           type: "POST",
           dataType: "json",
           data:form,
           contentType:false,
           processData:false,
           success:function(filename) {
               $("#upload-img").attr("src",'/images/avatar/'+filename);
               $("#upload-form").append("<input type='hidden' name='filename' value='"+filename+"'>");
           }
       })
    })
})
