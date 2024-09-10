function creditBadgeShow() {
    //img showing   
    $("#bonusCreditsImg").show();
    confettiSetting();

    setTimeout(function () {
        $("#bonusCreditsImg").hide();
    }, 10000);

    //Hide image
    $("#bonusCreditsImg").on("click", function () {
        $("#bonusCreditsImg").hide();
    });
}

/**
 * ConfettiSetting
 */
function confettiSetting() {
    const defaults = {
        spread: 360,
        ticks: 1000,
        gravity: 0,
        decay: 0.94,
        startVelocity: 30,
        shapes: ["heart"],
        colors: ["FFC0CB", "FF69B4", "FF1493", "C71585"],
    };

    confetti({
        ...defaults,
        particleCount: 150,
        scalar: 2,
    });

    confetti({
        ...defaults,
        particleCount: 25,
        scalar: 3,
    });

    confetti({
        ...defaults,
        particleCount: 10,
        scalar: 4,
    });
}
