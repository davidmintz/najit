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

const reset_btn = function(){
    console.log("hiding spinner?");
    spinner.attr("hidden",true);
    btn.removeAttr("disabled");
};

$(function() {
    btn.on("click",function(e){
        e.preventDefault();
        spinner.removeAttr("hidden");
        // takingcare@live.com expired

        status.removeClass("bg-warning").html("searching... ");
        $.post("/verify",$("form").serialize())
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
            
        }).then((response)=>{
            if (! response) { return; }
            console.log("I am the 2nd 'then'");
            if (! response.member) {
                console.log("no membership found. display a message, restore button");
                status.html("NAJIT member not found. Check your email address?").addClass("bg-warning");
                reset_btn();
            } else {
                // member found. expired?
                console.warn(`expired? ${response.expired}`);
                var expiration_date = dayjs(response.member.expiration_date).format("DD-MMM-YYYY");
                if (response.expired) {
                    status.html(`NAJIT's records indicate your membership expired on ${expiration_date}. Please renew!`)
                        .addClass("bg-warning");
                    reset_btn();
                    return;
                } // else, looks good
                console.log(`member found. expiration: ${response.member.expiration_date || "never"}`);
                console.log("formatted: "+dayjs(response.member.expiration_date).format("DD-MMM-YYYY"));
                status.html(`<span class="text-success fas fa-check"></span> Membership found with expiration ${expiration_date}`);
                console.warn("to be CONTINUED!")
                reset_btn();
            }
        });
    });
});