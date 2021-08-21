// assets/app.js
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// "yarn add jquery" followed by...
import $ from 'jquery';
// because I am lazy...
import dayjs from 'dayjs'; 

const btn = $("button[type=submit");
const btn_text = btn.children("span").text();
const status = $("#status");
const label = btn.children("span").first();
const spinner = $("#spinner");
const base_path = "/invitations/najit";


const reset_btn = function(){
    spinner.attr("hidden",true);
    btn.removeAttr("disabled");
};

const request_invitation = function(email) {
    $.post(base_path+"/invite",{email})
    .then(response=>{
        if (response.result.error && response.result.message) {
            status.append(
                `<br><span class="text-warning fas fa-exclamation-triangle"></span> ${response.result.message}`
            );
            return;
        }
        // all should be well
        if (response.result.emailed) {
            status.append(`<br><span class="text-success fas fa-check"></span> An invitation has been sent. 
                Please check your inbox in a couple minutes!`);
        }
    })
    .fail(response=>{
        // console.warn(`error is a : ${typeof error}`);
        // console.log(response.message);
        $("#status").append(
            `<br><span class="text-warning fas fa-exclamation-triangle"></span> An unexpected application error happened. Please try again later`
        );
    });

};
// FOR DEV
window.$ = $;
///

$(function() { 
    // const base_path = $("head").data().basePath;
    btn.on("click",function(e){
        e.preventDefault();
        spinner.removeAttr("hidden");
        // takingcare@live.com 
        // dheman_abdi@yahoo.com

        status.removeClass("bg-warning").html("searching... ");
        $.post(base_path+"/verify",$("form").serialize())
        .then(response=>{ 
            console.debug(`valid? ${response.valid}`);
            // check if there are validation errors;
            if (! response.valid) {
                status.empty();
                var keys = Object.keys(response.messages);
                keys.forEach((key,i)=>{
                    $(`#error-${key}`).text(response.messages[key]);
                });
                reset_btn();
                return;
            } else {
                $(".form-error").empty()
                return response;
            }
            
        })
        .then((response)=>{
            if (! response) { return; }
            if (! response.member) {
                status.html("NAJIT member not found. Check your email address?").addClass("bg-warning");
                reset_btn();
            } else {
                // member found. expired?
                var expiration_date = response.member.expiration_date === null ? "never"
                    : dayjs(response.member.expiration_date).format("DD-MMM-YYYY");
                if (response.expired) {
                    status.html(`NAJIT's records indicate your membership expired on ${expiration_date}. Please renew!`)
                        .addClass("bg-warning");
                    reset_btn();
                    return;
                } // else, looks good
                // console.log(`member found. expiration: ${response.member.expiration_date || "never"}`);
                status.html(`<span class="text-success fas fa-check"></span> Membership found with expiration date ${expiration_date}`);
                request_invitation(response.member.email);
                reset_btn();
            }
        });
    });
});