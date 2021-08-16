// assets/app.js
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

var btn = $("button[type=submit");
var btn_text = btn.children("span").text();

$(function() {
    btn.on("click",function(e){
        e.preventDefault();
        var label = btn.children("span");
        label.text("").addClass("fas fa-spinner fa-spin").attr("disabled",true)
            //.after(`<span id="status"> checking membership...</span>`);
        console.warn("shit was clicked");
        // console.warn( $("#najit_member_form_email").val())
        $.post("/verify",$("form").serialize())
        .then(response=>{ 
            console.debug(`valid? ${response.valid}`);
            // check if there are validation errors;
            if (! response.valid) {
                var keys = Object.keys(response.messages);
                keys.forEach((key,i)=>{
                    $(`#error-${key}`).text(response.messages[key]);
                });
                // and restore button if there are validation errors
                btn.removeAttr("disabled");
                label.removeClass("fas fa-spinner fa-spin").text(btn_text);
                return;
            } else {
                return response;
            }
            
        }).then((response)=>{
            console.log("I am the 2nd 'then'");
            if (! response.member) {
                console.log("no membership found. display message, restore button");
            } else {
                console.log(`member found. expiration: ${response.member.expiration_date}`);
            }
        });
    });
});