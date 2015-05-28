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
				['.sh','icon-file-code'],
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

function replaceFilename(item, filename){
	var needle = item.subject.split('_')[0];
	str = item.subjectformatted.full.split(needle)[0]+needle;

	var folderfile;
	if (filename.toLowerCase().indexOf('.') < 0){
		folderfile = 'a folder';
	}else{
		folderfile = 'a file';
	}
	str = str+' '+folderfile;

	switch(item.subject){
		case 'created_public':
			str = folderfile+' was'+item.subjectformatted.full.split('was')[1];
			str[0] = 'A';
			break;
		case 'shared_user_self':
		case 'shared_group_self':
		case 'shared_with_by':
				str=str+' with'+item.subjectformatted.full.split('with')[1];
			break;
		case 'shared_link_self':
				str=str+' via'+item.subjectformatted.full.split('via')[1];
			break;
	}
	return str;
}

'You have been invited to group %1$s by %2$s<div id="invite_div" style="display:none"><a href="#" id="accept" class="btn btn-default btn-flat" value = \'%1$s\'>Accept</a><a href="#" class="btn btn-default btn-flat" id="decline" value = \'%1$s\'>Decline</a></div>'

function replaceGroupname(item, filename){
	str = item.subjectformatted.full;
	switch(item.subject){
		case 'created_self':
		case 'deleted_self':
		case 'shared_user_self':
		case 'deleted_by':
			str = str.split('group');
			str = str[0]+'a group';
			break;
		case 'shared_with_by':
			if(str.indexOf("joined") > -1 || str.indexOf("invitation") > -1){
				str.replace(' group','');
				str.replace(filename,'a group');
			}else{
				str = str.split('group');
				str = str[0]+'a group';
			}
			break;
	}
	
	return str;
}

function files_app(item,filename,row){
	row.find('.fileicon').children('i').removeClass('icon-doc').addClass(ext2cssClass(filename));
	row.find('div.text-dark-gray').html(replaceFilename(item,filename));
	row.find('.notification-name').html(filename.substring(1));
	return row;
}

function user_group_admin_app(item,filename,row){
	row.find('.fileicon').children('i').removeClass('icon-doc text-bg').addClass('icon-users deic_green icon').attr('style','color: rgb(181, 204, 45);');
	row.find('div.text-dark-gray').html(replaceGroupname(item,filename));
	
	if(item.subject == 'shared_with_by' && item.subjectformatted.full.indexOf('id="invite_div"') > -1){
		row.find('.notification-name').html(item.subjectparams[0]+' by '+item.user);
		row.find('.notification-name').after( '<div id'+item.subjectformatted.full.split('<div id')[1] );
	}else{
		row.find('.notification-name').html(item.subjectparams[0]);
	}
	return row;
}

function processCase(item,filename,row){
	switch(item.app){
		case 'files':
			return files_app(item,filename,row);
			break;
		case 'user_group_admin':
			return user_group_admin_app(item,filename,row);
			break;
		default:
	}
}

function addRow(item,filename){
	var row=$('li.notifications').find('li.template').clone();
	row.removeClass('template');
	if (item['seen']==false) {
		row.addClass('unread');
	}else{
		row.addClass('read');
	};
	row.removeClass('hidden');
	row.children('a').attr('href',item.link);
	row.find('.avatardiv').avatar(item.user, 28)
	row.find('span.text-light-gray').html(timeDifference(Date.now(),item.timestamp*1000.) ); 
	row = processCase(item,filename,row);
	$('li.notifications').children('ul').append(row);
}

function addActivityRow(){
	var row=$('li.notifications').find('li.template').clone();
	row.removeClass('template');
	row.addClass('result');
	row.children('a').attr('href', OC.generateUrl('/apps/activity'));				
	row.find('.row').children().remove();				
	row.find('.row').append('<div class="col-sm-11 col-sm-offset-1 col-xs-10 col-sx-offset-2"><div class="text-dark-gray"><i class="icon-flash deic_green icon"></i>All Activities</div></div>');
	row.removeClass('hidden');
	$('li.notifications').children('ul').append(row);
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
				//addActivityRow(); // this is fixing a bug that the first Item is never displayed
				$.each(result, function(index,item) {
					if(index!='status'){
						if($.isArray(item.subjectparams[0])){
							$.each(item.subjectparams[0], function(index2,filename){
								addRow(item,filename);
							});
						}else{
							addRow(item,item.subjectparams[0]);
						}		
					}
				});
				addActivityRow();
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


