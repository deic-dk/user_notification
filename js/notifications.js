function timeDifference(current, previous) {

  var msPerMinute = 60. * 1000;
  var msPerHour = msPerMinute * 60.;
  var msPerDay = msPerHour * 24.;
  var msPerMonth = msPerDay * 30.;
  var msPerYear = msPerDay * 365.;

  var elapsed = current - previous;

  if (elapsed < msPerMinute) {
	return Math.round(elapsed/1000.) + ' seconds';   
  }

  else if (elapsed < msPerHour) {
	return Math.round(elapsed/msPerMinute) + ' minutes';   
  }

  else if (elapsed < msPerDay ) {
	return Math.round(elapsed/msPerHour ) + ' hours';   
  }

  else if (elapsed < msPerMonth) {
	return Math.round(elapsed/msPerDay) + ' days';   
  }

  else if (elapsed < msPerYear) {
	return Math.round(elapsed/msPerMonth) + ' months';   
  }

  else {
	return Math.round(elapsed/msPerYear ) + ' years';   
  }
}

$(document).ready(function() {
  var parent=$('<li class="notifications dropdown">');
  $('header').find('ul.navbar-nav').prepend(parent);
  parent.load(OC.filePath('user_notification','templates','notifications.php'),function(){
	$.ajax({
	  url: OC.filePath('user_notification', 'ajax', 'getNew.php'),
	  success: function(result) {
		var count = Object.keys(result).length-1;
		if(count > 0){
		  $('span.num-notifications').toggleClass('hidden').html(count);
		}	
	  }
	});

  });


  $('ul.navbar-nav').on('click','li.notifications', function() {
	$.ajax({
	  url: OC.filePath('user_notification', 'ajax', 'getNew.php'),
	  success: function(result) {
		$.each(result, function(index,item) {
		  var row=$('li.notifications').find('li.template').clone();
		  row.removeClass('template');
		  row.addClass('result');
		  row.addClass('unread');
		  row.children('a').attr('href',item.link);
	  	  row.find('.avatardiv').children('img').attr('src','/index.php/avatar/'+item.user);
		  
		  row.find('div.text-dark-gray').html(item.subjectformatted.full);
		  row.find('i.text-bg').addClass('icon-doc');


		  row.find('span.text-light-gray').html(timeDifference(Date.now(),item.timestamp*1000.) ); 
		  row.removeClass('hidden');
		  $('li.notifications').children('ul').append(row);
		});



	  }

	});


  });



})
