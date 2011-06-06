$(document).ready(
		function()
		{
			$('#dock').Fisheye(
				{
					maxWidth: 10,
					items: 'a',
					itemsText: 'span',
					container: '.dock-container',
					itemWidth: 35,
					proximity: 30,
					halign : 'right'
				}
			);
		}
	);

