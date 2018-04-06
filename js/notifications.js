var PRIORITY_MIN = 0;

function timeDifference(current, previous) {

	var msPerMinute = 60. * 1000;
	var msPerHour = msPerMinute * 60.;
	var msPerDay = msPerHour * 24.;
	var msPerMonth = msPerDay * 30.;
	var msPerYear = msPerDay * 365.;

	var elapsed = current - previous;
	var relapsed;
	var unit;
	
	if (elapsed < msPerMinute) {
		relapsed = Math.round(elapsed/1000.) ;
		unit = ' second';
	}

	else if (elapsed < msPerHour) {
		relapsed = Math.round(elapsed/msPerMinute)
		unit = ' minute';
	}

	else if (elapsed < msPerDay ) {
		relapsed = Math.round(elapsed/msPerHour )
		unit = ' hour';
	}

	else if (elapsed < msPerMonth) {
		relapsed = Math.round(elapsed/msPerDay)
		unit = ' day';
	}

	else if (elapsed < msPerYear) {
		relapsed = Math.round(elapsed/msPerMonth)
		unit = ' month';
	}

	else {
		relapsed = Math.round(elapsed/msPerYear )
		unit = ' year';
	}
	return relapsed+unit+(relapsed==1?'':'s')
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
	// All this is just for displaying folder or file and irrelevant for non-English.
	if(item.subjectformatted.full.split(needle).length==1){
		return item.subjectformatted.full;
	}
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

function replaceInvoicename(item){
  str = item.subjectformatted.full;
  switch(item.subject){
  	case 'created_self':
	  str = 'You have a new invoice for';
	  break;
	case 'completed_self':
	  str = 'You completed a payment';
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
	var groupIcon = row.find('.fileicon').children('i').removeClass('icon-doc text-bg').addClass('icon-users deic_green icon').attr('style','color: rgb(181, 204, 45);');
	row.find('div.text-dark-gray').html(item.subjectformatted.full);
	row.find('.notification-name').html(item.subjectparams[0]);
	row.children('a').attr('href', OC.webroot+'/index.php/apps/user_group_admin');
	var notificationRow = row.find('.notification-name').addClass('invite_here');
	var inviteRow = row.find('.invite_div');
	inviteRow.attr('activity_id', item.activity_id);
	inviteRow.show()
	notificationRow.after(inviteRow);
	return row;
}

function files_accounting_app(item,filename,row){
	row.find('div.text-dark-gray').html(replaceInvoicename(item));
	row.find('.fileicon').children('i').removeClass('icon-doc').addClass('icon-chart-area');
	row.find('.notification-name').html(item.subjectparams[0]);
	var paidRow = row.find('.unpaid_invoice');
	paidRow.attr('activity_id', item.activity_id);
	return row;
}

function files_sharding_app(item,filename,row){
	row.find('div.text-dark-gray').html(item.subject);
	row.find('.avatardiv').remove();
	row.children('a').attr('href', OC.webroot+'/index.php/apps/activity');
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
		case 'files_accounting':
			return files_accounting_app(item, filename, row);
			break;
		case 'files_sharding':
			return files_sharding_app(item, filename, row);
			break;
		default:
			return row;
	}
}

function addRow(item, filename){
	var row=$('li.notifications').find('li.template').clone();
	row.removeClass('template');
	if (parseInt(item['priority'], 10)>PRIORITY_MIN) {
		row.addClass('unread');
	}
	else{
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
	var row = $('li.notifications').find('li.template').clone();
	row.removeClass('template');
	row.addClass('result');
	row.children('a').attr('href', OC.generateUrl('/apps/activity'));
	row.find('.row').children().remove();
	row.find('.row').append('<div class="col-sm-11 col-sm-offset-1 col-xs-10 col-sx-offset-2"><div class="text-dark-gray"><i class="icon-flash deic_green icon"></i>'+
			t('user_notification', 'All activities')+'</div></div>');
	row.removeClass('hidden');
	$('li.notifications').children('ul').append(row);
}

$(document).ready(function() {
	var parent=$('<li class="notifications dropdown">');
	$('header').find('ul.navbar-nav .pull-right').before(parent);
	parent.load(OC.filePath('user_notification','templates','notifications.php'),function(){
		$.ajax({
			url: OC.filePath('user_notification', 'ajax', 'getNew.php'),
			success: function(result) {
				var count=0;
				for (i = 0; i < Object.keys(result).length-1; i++) {
					var row = result[i];
					/*row is sometimes undefined - weird... TODO: investigate*/
			    if(typeof row === 'undefined' || parseInt(row['priority'], 10)>PRIORITY_MIN){
			    	count +=1;
			    }
				}
				if(count > 0){
					$('span.num-notifications').toggleClass('hidden').html(count);
				}
				else{
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
							$.each(item.subjectparams[0], function(index2,name){
								addRow(item, name);
							});
						}
						else{
							addRow(item, item.subjectparams[0]);
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
