import $ from 'jquery';
import progressBar from 'block_th_activityrating/progressbar';

export const init = () => {
    $(document).ready(function(){
		$('.progressbar1').progressBar({
			shadow: false,
			percentage: false,
			animation: true,
			barColor: "#2441e7",
		});
		$('.progressbar2').progressBar({
			shadow: false,
			percentage: false,
			animation: true,
			barColor: "#2441e7",
		});
		$('.progressbar3').progressBar({
			shadow: false,
			percentage: false,
			animation: true,
			animateTarget: true,
			barColor: "#2441e7",
		});
		$('.progressbar4').progressBar({
			shadow: false,
			percentage: false,
			animation: true,
			animateTarget: true,
			barColor: "#2441e7",
		});
		$('.progressbar5').progressBar({
			shadow: false,
			percentage: false,
			animation: true,
			animateTarget: true,
			barColor: "#2441e7",
		});
	});
};