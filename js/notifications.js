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

function getExtensions(){
	var ext  = [
				// images
				['.jpeg','icon-file-image'],
				['.jpg','icon-file-image'],
				['.png','icon-file-image'],
				['.gif','icon-file-image'],
				// docs
				['.odt','icon-file-word'],
				['.doc','icon-file-word'],
				['.docx','icon-file-word'],
				// ppt
				['.ppt','icon-file-powerpoint'],
				['.pptx','icon-file-powerpoint'],
				// excel
				['.xls','icon-file-excel'],
				['.xlc','icon-file-excel'],
				// pdf
				['.pdf','icon-file-pdf'],
				// audio
				['.mp3','icon-file-audio'],
				['.ogg','icon-file-audio'],
				['.wav','icon-file-audio'],
				['.flac','icon-file-audio'],
				['.m4a','icon-file-audio'],
				['.wma','icon-file-audio'],
				['.aiff','icon-file-audio'],
				// video
				['.m4v','icon-file-video'],
				['.wmv','icon-file-video'],
				['.mpeg','icon-file-video'],
				['.mpg','icon-file-video'],
				['.mkv','icon-file-video'],
				['.flv','icon-file-video'],
				['.avi','icon-file-video'],
				// archive
				['.zip','icon-file-archive'],
				['.rar','icon-file-archive'],
				// code
				['.m','icon-file-code'],
				['.py','icon-file-code']];
	return ext;
}

function ext2cssClass (filename) {
	var ext = getExtensions();

	for (i = 0; i < ext.length; i++) {
    	if (filename.toLowerCase().indexOf(ext[i][0]) >= 0){
			return ext[i][1];
		}
	}
	if (filename.toLowerCase().indexOf('.') < 0){
		return 'icon-folder'
	}else{
		return 'icon-doc'
	}
}
function replaceFilename(str, filename){
	var needle;
	if (filename.toLowerCase().indexOf('.') < 0){
		needle = 'a folder';
	}else{
		needle = 'a file';
	}
	str = str.replace(filename.substring(1), needle);
	return str;
}

$(document).ready(function() {
	var parent=$('<li class="notifications dropdown">');
	$('header').find('ul.navbar-nav').prepend(parent);
	parent.load(OC.filePath('user_notification','templates','notifications.php'),function(){
		$.ajax({
			url: OC.filePath('user_notification', 'ajax', 'getNew.php'),
			success: function(result) {
				var count=0;
				for (i = 0; i < Object.keys(result).length-1; i++) {
					var row = result[i];
				    if(row['seen']==false){
				    	count +=1;
				    }
				}
				if(count > 0){
					$('span.num-notifications').toggleClass('hidden').html(count);
				}else{
					$('.bell').removeClass('ringing');
				}
			}
		});
	});

	$('ul.navbar-nav').on('click','li.notifications', function() {
		$.ajax({
			url: OC.filePath('user_notification', 'ajax', 'getNew.php'),
			success: function(result) {
				$('li.notifications').find('li:not(.template)').remove();
				$.each(result, function(index,item) {
					if(index!='status'){
						var row=$('li.notifications').find('li.template').clone();
						row.removeClass('template');
						row.addClass('result');
						if (item['seen']==false) {
							row.addClass('unread');
						}else{
							row.addClass('read');
						};						
						row.children('a').attr('href',item.link);
						row.find('.fileicon').children('i').removeClass('icon-doc').addClass(ext2cssClass(item.file));
						row.find('.avatardiv').avatar(item.user, 28)
						row.find('div.text-dark-gray').html(replaceFilename(item.subjectformatted.full,item.file));
						row.find('span.text-dark-gray').html(item.file.substring(1));
						row.find('i.text-bg').addClass('icon-doc');
						row.find('span.text-light-gray').html(timeDifference(Date.now(),item.timestamp*1000.) ); 
						row.removeClass('hidden');						
						$('li.notifications').children('ul').append(row);
					}
				});
				var row=$('li.notifications').find('li.template').clone();
				row.removeClass('template');
				row.addClass('result');
				row.children('a').attr('href', OC.generateUrl('/apps/activity'));				
				row.find('.row').children().remove();				
				row.find('.row').append('<div class="col-sm-11 col-sm-offset-1 col-xs-10 col-sx-offset-2"><div class="text-dark-gray"><i class="icon-flash deic_green icon"></i>All Activities</div></div>');
				row.removeClass('hidden');
				$('li.notifications').children('ul').append(row);

			}
		});
		$.ajax({
			url: OC.filePath('user_notification', 'ajax', 'seen.php'),
			success: function(result) {
				$('.bell').removeClass('ringing');
			}
		});
	});
})


