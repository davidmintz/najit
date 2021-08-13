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
    btn.on("click",function(e){ // return;
        e.preventDefault();
        var label = btn.children("span");
        btn.children("span").text("").addClass("fas fa-spinner fa-spin").attr("disabled",true);
        console.warn("shit was clicked");
        // console.warn( $("#najit_member_form_email").val())
        $.post("/verify",$("form").serialize())
        .then(r=>{ 
            console.log(r);
            // check if there are validation errors;
            label.removeClass("fas fa-spinner fa-spin").text(btn_text);
            // and maybe restore button if there are validation errors

        });
        // $.post("/invite",{
        //     email : $("#najit_member_form_email").val(),
        //     _token : $("najit_member_form_token").val()
        // }).then(r=>console.log(r));
    });
});