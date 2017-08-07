$(document).ready(function() {

    var audioPlayer = $("#audio-player");

    function pad(n) {
        if (n < 10) {
            return "0" + n;
        } else {
            return n.toString();
        }
    }

    function formatTime(seconds) {
        seconds = Math.floor(seconds);
        if (seconds < 60) {
            return "00:" + pad(seconds)
        }
        else {
            return pad(parseInt(seconds / 60, 10)) + ":" + pad(seconds % 60);
        }
    }

    function getTracksDurations() {
        var tracks = $(".track-list li");
        var i = 0;
        $(tracks).each(function () {
            var track = this, url = $(track).data("url");
            var a = '<audio src="' + url + '" id="temp-player-' + i + '">';
            $(".available-audios").append(a);
            $("#temp-player-" + i).on("loadedmetadata", function () {
                var str = formatTime(this.duration);
                $(this).remove();
                $(track).find(".time").html(str);
            });
            i++;
        });
    }

    window.RAF = (function () {
        return window.requestAnimationFrame ||
            window.webkitRequestAnimationFrame ||
            window.mozRequestAnimationFrame ||
            function (callback) {
                window.setTimeout(callback, 1000 / 60);
            };
    })();

    function updatePlayer() {
        var percent = audioPlayer.get(0).currentTime / audioPlayer.get(0).duration * 100;
        if(!isNaN(percent)){
            $(".progress").val(percent);
            $(".timer").html(formatTime(audioPlayer.get(0).currentTime));
        }
    }

    $(function () {
        getTracksDurations();

        $("li.track-info").on("click", function () {

            $(this).addClass("active")
                .siblings().removeClass("active");

            var ad = $(this).data("url");
            $(audioPlayer).attr("src", ad);
            $(".cover").css({"background-image": "linear-gradient(to bottom, rgba(0, 0, 0, .75), rgba(0, 0, 0, 0)), url(" + $(this).data("cover") + ")"});
            audioPlayer.get(0).play();
        });

        $(".play").on("click", function () {
            audioPlayer.get(0).play();
            $(this).hide();
            $(".pause").show();
        });

        $(".pause").on("click", function () {
            audioPlayer.get(0).pause();
            $(this).hide();
            $(".play").show();
        });

        $(".stop").on("click", function () {
            audioPlayer.get(0).pause();
            audioPlayer.get(0).currentTime = 0;
        });

        $(".prev").on("click", function () {
            $(".track-list li.active:not(:first-child)").removeClass("active")
                .prev().addClass("active").click();
        });

        $(".next").on("click", function () {
            $(".track-list li.active:not(:last-child)").removeClass("active")
                .next().addClass("active").click();
        });

        $(".volume-down").on("click", function () {
            if(audioPlayer.get(0).volume > 0){
                audioPlayer.get(0).volume -= .1;
                console.log(audioPlayer.get(0).volume)
            }

        });
        $(".volume-up").on("click", function () {

            if(audioPlayer.get(0).volume < 1){
                audioPlayer.get(0).volume += .1;
                console.log(audioPlayer.get(0).volume)
            }


            //console.log(audioPlayer.get(0).volume);
        });

        $(".progress").on("click", function (e) {
            var percent = (e.clientX - $(this).offset().left) / $(this).width() * 100;
            audioPlayer.get(0).currentTime = percent * audioPlayer.get(0).duration / 100;
        });

        $(audioPlayer).on("pause", function () {
            $(".pause").hide();
            $(".play").show();
        });

        $(audioPlayer).on("play", function () {
            $(".play").hide();
            $(".pause").show();
        });

        $(audioPlayer).on("ended", function () {
            $(".track-list li.active:not(:last-child)").next().click();
        });

        //load first track
        $(audioPlayer).attr("src", $(".track-list li.active").data("url"));
        $(".cover").css({"background-image": "linear-gradient(to bottom, rgba(0, 0, 0, .75), rgba(0, 0, 0, 0)), url(" + $(".track-list li.active").data("cover") + ")"});

        (function animLoop() {
            RAF(animLoop);
            updatePlayer();
        })();
    });
});