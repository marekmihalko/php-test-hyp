import Swal from 'sweetalert2'
import axios from 'axios';

document.addEventListener('DOMContentLoaded', function () {
    const body = document.querySelector('body');
    body.addEventListener('click', event => {
        if (event.target.classList.contains('btn-delete-comment')) {
            deleteComment(event);
        }
        if (event.target.classList.contains('btn-reply-comment')) {
            createReplyCommentContainer(event);
        }
        if (event.target.classList.contains('btn-create-comment')) {
            createComment(event);
        }
        if (event.target.classList.contains('btn-edit-comment')) {
            toggleEditableCommentSendBtn(event);
        }
        if (event.target.classList.contains('btn-edit-comment-send')) {
            editComment(event);
        }
        if (event.target.classList.contains('btn-full-wipe-comments')) {
            fullWipeComments(event);
        }
    });
});

function swalLoading() {
    Swal.fire({
        icon: '',
        title: 'Loading...',
        text: '',
        showConfirmButton: false,
    });
}

function reloadPage() {
    window.location.reload();
}

function axiosHandle($method, $url, $defaultMessage, paramsObject = {}) {
    axios({
        method: $method,
        url: $url,
        params: paramsObject
    }).then(function (response) {
        Swal.fire(response?.data?.message || $defaultMessage, '', 'success');
        reloadPage();
    }).catch(function (error) {
        Swal.fire(error?.response?.data?.message || error.message, '', 'error');
    });
}

function fullWipeComments(event) {
    let articleId = event.target.getAttribute('data-article-id');

    Swal.fire({
        title: 'You really want full wipe comments?',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: `No`,
    }).then((result) => {
        if (result.isConfirmed) {
            swalLoading();
            axiosHandle('delete', '/comment/wipe', 'Wiped', {
                article: articleId
            });
        }
    })
}

function editComment(event) {
    let commentId = event.target.getAttribute('data-comment-id');
    let cardBodyElement = event.target.parentElement.parentElement;
    let commentTextElement = cardBodyElement.querySelector('.comment-text');

    Swal.fire({
        title: 'Confirm editing?',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: `No`,
    }).then((result) => {
        if (result.isConfirmed) {
            swalLoading();
            axiosHandle('patch', `/comment/${commentId}`, 'Edited', {
                text: commentTextElement.innerHTML
            });
        }
    })
}

function toggleEditableCommentSendBtn(event) {
    let commentId = event.target.getAttribute('data-comment-id');
    let cardBodyElement = event.target.parentElement.parentElement;
    let commentTextElement = cardBodyElement.querySelector('.comment-text');
    let commentSendBtn = cardBodyElement.querySelector('.btn-edit-comment-send')
    if (commentSendBtn) {
        commentSendBtn.remove();
        commentTextElement.contentEditable = 'false';
    } else {
        event.target.before(crateSendEditBtn(commentId));
        commentTextElement.contentEditable = 'true';
        commentTextElement.focus();
    }
}

function crateSendEditBtn(commentId) {
    let btnSend = document.createElement('div');
    btnSend.innerHTML = 'Send Edit';
    btnSend.className = 'btn btn-warning btn-sm btn-edit-comment-send';
    btnSend.dataset.commentId = commentId;
    return btnSend;
}

function createReplyCommentContainer(event) {
    let commentId = event.target.getAttribute('data-comment-id');
    let cardBodyElement = event.target.parentElement.parentElement;
    let replyCommentContainerElement = cardBodyElement.querySelector('.comment-reply-container');

    if (replyCommentContainerElement.innerHTML) {
        replyCommentContainerElement.innerHTML = '';
    } else {
        replyCommentContainerElement.innerHTML = document.querySelector('.comment-form').outerHTML;
        replyCommentContainerElement.querySelector("[name='parent']").value = commentId;
    }
}

function createComment(event) {
    event.preventDefault();

    let formElement = event.target.parentElement.parentElement;
    let missingDataInfoElement = formElement.querySelector('.missing-comment-data');
    let formData = new FormData(formElement);

    missingDataInfoElement.innerHTML = '';
    swalLoading();

    axios({
        method: 'post',
        url: '/comment/',
        data: formData,
    }).then(function (response) {
        Swal.fire(response?.data?.message || 'Created', '', 'success');
        reloadPage();
    }).catch(function (error) {
        Swal.close();
        let errorInfo = error?.response?.data?.data?.map(error => `<div>${error.message}</div>`);
        missingDataInfoElement.innerHTML = errorInfo.join("");
    });
}

function deleteComment(event) {
    let commentId = event.target.getAttribute('data-comment-id');

    Swal.fire({
        title: 'Do you want DELETE comment?',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: `No`,
    }).then((result) => {
        if (result.isConfirmed) {
            swalLoading();
            axiosHandle('delete', `/comment/${commentId}`, 'Deleted');
        }
    })
}