/**
 * Cricboard JS
 */
(function( $ ) {
	//Move upload form before runs box
	$(".comment-form-attachment").insertBefore(".my_runs-field");
	
    $("#league-filter").change(function() {
        var selectedVal = $(this).children('option:selected').val();        
        if(selectedVal == "") {
            $(".leaderboard-container .leaderboard-row").removeClass('hide-league');
        } else {
            $(".leaderboard-container .leaderboard-row").each(function() {
                if($(this).attr("data-league") != selectedVal) {
                    $(this).addClass('hide-league');
                } else {
                    $(this).removeClass('hide-league');
                }
            });
        }
    });
    
    $("#opponent-filter").change(function() {
        var selectedVal = $(this).children('option:selected').val();        
        if(selectedVal == "") {
            $(".leaderboard-container .leaderboard-row").removeClass('hide-opponent');
        } else {
            $(".leaderboard-container .leaderboard-row").each(function() {
                if($(this).attr("data-opponent") != selectedVal) {
                    $(this).addClass('hide-opponent');
                } else {
                    $(this).removeClass('hide-opponent');
                }
            });
        }
    });
	
	$(window).load(function() {
		//Countdown timer
		var deadline = $('#deadline').val();		
		
		//if(deadline != "") {
				var deadline_vals = deadline.split("-");
				var day = parseInt(deadline_vals[0]);
				var month = parseInt(deadline_vals[1]);
				var year = parseInt(deadline_vals[2]);
				
				var countDownDate = new Date(year, (month-1), day).getTime();
				
				// Update the count down every 1 second
				var x = setInterval(function() {		
				// Get todays date and time
				var now = new Date().getTime();
				
				// Find the distance between now and the count down date
				var distance = countDownDate - now;
				
				// Time calculations for days, hours, minutes and seconds
				var days = Math.floor(distance / (1000 * 60 * 60 * 24));
				var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
				var seconds = Math.floor((distance % (1000 * 60)) / 1000);
				
				// Display the result in the element with id="demo"
				document.getElementById("deadline_placeholder").innerHTML = days + " Day(s) " + hours + " Hour(s) " + minutes + " Minute(s) " + seconds + " Second(s) Left";
				
				// If the count down is finished, write some text 
				if (distance < 0) {
				  clearInterval(x);
				  document.getElementById("deadline_placeholder").innerHTML = "Expired";
				}
			}, 1000);
		//}
	});
})( jQuery );