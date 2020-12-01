$(function() {
    $("#attendance").autocomplete({
        source: "/autocomplete",
        minLength: 2
    });
});