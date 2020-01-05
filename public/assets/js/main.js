$(document).ready(function () {
    let replyDiv = "<div class=\"media media-post\">\n" +
        "                                            <a class=\"pull-left author\" href=\"#pablo\">\n" +
        "                                                <div class=\"avatar\">\n" +
        "                                                    <img class=\"media-object\" alt=\"64x64\" src=\"{{asset('assets/dashboard/img/faces/avatar.jpg')}}\">\n" +
        "                                                </div>\n" +
        "                                            </a>\n" +
        "                                            <div class=\"media-body\">\n" +
        "                                                <textarea class=\"form-control\" placeholder=\"Ecrivez quelque chose...\" rows=\"2\"></textarea>\n" +
        "                                                <div class=\"media-footer\">\n" +
        "                                                    <a href=\"#pablo\" class=\"btn btn-primary btn-round btn-wd pull-right\">Publier</a>\n" +
        "                                                </div>\n" +
        "                                            </div>\n" +
        "                                        </div>";


    $('.replyButton').each(function () {
        $(this).click(function () {
            let footer = $(this).parent();
            footer.each(function () {
                let reply = footer.next();
                if(reply.hasClass("media-post")){
                    reply.remove()
                }else{
                    footer.after(replyDiv)
                }
            })
        })
    })
});