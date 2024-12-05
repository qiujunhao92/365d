
var lockProcess = 0;
let pageUrl = location.href;

function validateEmail(email) {
    // 定义正则表达式模式
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    // 测试电子邮件地址是否匹配模式
    return emailPattern.test(email);
}
function getEmailUsername(email) {
    // 获取 @ 符号的索引
    const atIndex = email.indexOf('@');
    // 使用 substring 方法获取 @ 符号前面的部分
    return email.substring(0, atIndex);
}

function gallery_voting_vote(page_id, attachment_id, source_url) {
    jQuery('span#gallery-voting-loading-' + attachment_id).css('display', 'inline-block');
    jQuery.post(galleryvotingajaxurl + "?action=galleryvotingvote", {page_id:page_id, attachment_id:attachment_id,source_url: source_url, page_url : pageUrl}, function(response) {
        jQuery('span#gallery-voting-loading-' + attachment_id).hide();

        if (response.success == true) {
            jQuery('span#gallery-voting-count-' + attachment_id).text(response.count);
        } else {
            alert(response.error);
        }
    });
}
function gallery_voting_comment(page_id, attachment_id, source_url) {
    if (lockProcess) {
        alert('Submitting data, please waiting a comment...')
        return;
    }
    let limit =  checkLimit(attachment_id);
    if (limit) {
        return;
    }

    let comment = jQuery('#your-message-' + attachment_id).val();
    let email = jQuery('#your-email-' + attachment_id).val();

    if (!validateEmail(email)) {
        alert('please input a valid email!');
        return;
    }
    if (comment.length <= 3) {
        alert('Comment length should not be less than 3!');
        return;
    }
    jQuery('div.commentSub-' + attachment_id).hide();


    //   let cusHtml = '<li class="cpcomment">(' + getEmailUsername(email) + ')'+ comment + '</li>';
    let cusHtml = '<li class="cpcomment">' + comment + '</li>';
    let obj = jQuery('.wpd-comment-list-' + attachment_id + ' ul');
    if (obj.length >0) {
        obj.prepend(cusHtml)
    } else {
        jQuery('.wpd-comment-list-' + attachment_id).html('<div><span class="title"> Comments</span></div><ul>' + cusHtml + '</ul>');
    }
    jQuery('.wpd-comment-list-' + attachment_id + ' ul')
    lockProcess = 1;
    jQuery.post(galleryvotingajaxurl + "?action=galleryvotingcomment", {page_id:page_id, attachment_id:attachment_id,source_url: source_url, email:email, comment:comment, page_url : pageUrl}, function(response) {
        if (response.success == true) {
            jQuery('.wpd-comment-list-' + response.attachment_id).html(response.html);
            updateTotalCount(response.attachment_id);
        } else {
            alert(response.error);
        }
        lockProcess = 0;
    });
}

function gallery_cancel_comment(page_id, attachment_id) {
    jQuery('div.commentSub-' + attachment_id).hide();
}

function checkLimit(attachment_id)
{
    let totalLimit = parseInt(jQuery('.spanComment-' +attachment_id).attr('data-total-comment-limit'));
    let sameLimit =  parseInt(jQuery('.spanComment-' +attachment_id).attr('data-same-comment-limit'));
    let totalCount =  parseInt(jQuery('.spanComment-' +attachment_id).attr('data-ip-comments'));
    let currentIPCount = parseInt(jQuery('.wpd-comment-list-' +attachment_id).attr('data-sameip'));

    let limited = false;
    if (currentIPCount >= sameLimit) {
        if (sameLimit === 1) {
            alert('You had already commented for it!');
        } else {
            alert('You have already commented '+ sameLimit +' times for it');
        }
        limited = true;
    } else if (totalCount >= totalLimit) {
        alert('You have already commented '+ totalLimit +' times in this page');
        limited = true;
    }
    return limited;
}

function updateTotalCount(attachment_id)
{
    let totalCount =  parseInt(jQuery('.spanComment-' +attachment_id).attr('data-ip-comments'));
    let currentIPCount = parseInt(jQuery('.wpd-comment-list-' +attachment_id).attr('data-sameip'));
    jQuery('.spanComment').attr('data-ip-comments', totalCount+1);
    jQuery('.wpd-comment-list-' +attachment_id).attr('data-sameip', currentIPCount+1);
}

function onCommentForm(page_id, attachment_id, sourceUrl)
{
    if (jQuery('div.commentSub-' + attachment_id).is(":visible")) {
        gallery_cancel_comment(page_id, attachment_id);
        return;
    }
    jQuery('div.commentSub-' + attachment_id).show();
}
jQuery(document).ready(function(){
    jQuery('body').addClass('gallery-vote-page');
})