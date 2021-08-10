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

console.log('Hello Webpack Encore! Edit me in assets/app.js');

$(function() {
    $("button[type=submit").on("click",function(e){ // return;
        e.preventDefault();
        console.warn("shit was clicked");
        // console.warn( $("#najit_member_form_email").val())
        $.post("/invite",$("form").serialize())
        .then(r=>console.log(r))
        // $.post("/invite",{
        //     email : $("#najit_member_form_email").val(),
        //     _token : $("najit_member_form_token").val()
        // }).then(r=>console.log(r));
    });
});