// This file contains javascript that is specific to the /profile controller.
jQuery(document).ready(function($) {

   // Hijack "add message" clicks and handle via ajax...
   $.fn.handleMessageForm = function() {
      this.click(function() {
         var button = this;
         $(button).attr('disabled', 'disabled');
         var frm = $(button).parents('form').get(0);
         var textbox = $(frm).find('textarea');
         // Post the form, and append the results to #Discussion, and erase the textbox
         var postValues = $(frm).serialize();
         postValues += '&DeliveryType=VIEW&DeliveryMethod=JSON'; // DELIVERY_TYPE_VIEW
         postValues += '&'+button.name+'='+button.value;
         var prefix = textbox.attr('name').replace('Message', '');
         // Get the last message id on the page
         var messages = $('ul.Conversation li');
         var lastMessage = $(messages).get(messages.length - 1);
         var lastMessageID = $(lastMessage).attr('id');
         postValues += '&' + prefix + 'LastMessageID=' + lastMessageID;
         $(button).html('<span class="TinyProgress">&#160;</span>');
         $.ajax({
            type: "POST",
            url: $(frm).attr('action'),
            data: postValues,
            dataType: 'json',
            error: function(xhr) {
               gdn.informError(xhr);
            },
            success: function(json) {
               // Remove any old errors from the form
               $(frm).find('div.Errors').remove();

               if (json.ErrorMessages) {
                  $(frm).prepend(json.ErrorMessages);
                  json.ErrorMessages = null;
               }

               if (json.FormSaved) {
                  // Clean up the form
                  clearMessageForm();

                  console.log(json.Data)

                  // And show the new comments
                  $('ul.Conversation').appendTrigger(json.Data);

                  // Remove any "More" pager links
                  $('#PagerMore').remove();

                  // And scroll to them
                  var target = $('#' + json.MessageID);
                  if (target.offset()) {
                     $('html,body').animate({scrollTop: target.offset().top}, 'fast');
                  }

                  // Let listeners know that the message was added.
                  $(document).trigger('MessageAdded');
                  $(frm).triggerHandler('complete');

                  gdn.inform(json);
               }
            },
            complete: function(XMLHttpRequest, textStatus) {
               // Remove any spinners, and re-enable buttons.
               $('.MessageList li.empty').remove();
               $('span.TinyProgress').remove();
               $(button).html('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m13.887 21.348-3.68-5.586L16.51 7.99c.18-.224-.047-.428-.25-.249l-7.776 6.323-5.59-3.7c-.724-.478-.57-1.56.217-1.826l16.94-5.65c.789-.267 1.56.504 1.292 1.292l-5.653 16.93c-.267.81-1.349.94-1.803.24z" fill="#fff"/></svg>')
               $(frm).find(':submit').removeAttr("disabled");
            }
         });
         return false;

      });
   }

   $(document).on('click', '.delete-user', function() {
      var conversationID = $(this).attr('data-conversation-id');
      var userID = $(this).attr('data-user-id');

      console.log(conversationID)

      var fd = new FormData();
      fd.append('conversationID', conversationID);
      fd.append('userID', userID);

      $.ajax({
         type: "GET",
         url: gdn.url('messages/leave'),
         data: {"conversationID": conversationID, "userID": userID},
         error: function(xhr) {
            gdn.informError(xhr);
         },
         success: function(json) {
            $('.InThisConversation li[data-userid='+userID+']').remove();
         },
         complete: function(XMLHttpRequest, textStatus) {
            console.log('complete')
         }
      });
   })
   $('#Form_ConversationMessage :submit').handleMessageForm();

   // Utility function to clear out the message form
   function clearMessageForm() {
      $('div.Popup').remove();
      var frm = $('#Form_ConversationMessage');
      frm.find('textarea').val('');
      frm.trigger('clearCommentForm');

     // Dispatch a native event for things that don't use jquery
      var event = document.createEvent('CustomEvent');
      event.initCustomEvent('X-ClearCommentForm', true, false, {});
      frm[0].dispatchEvent(event);
      frm.find('div.Errors').remove();
      $('div.Information').fadeOut('fast', function() { $(this).remove(); });
   }

   $.fn.userTokenInput = function() {
      $(this).each(function() {
         /// Author tag token input.
           var $author = $(this);
            if (this.dataset.users) {
               var author = JSON.parse(this.dataset.users);
           }
           // gdn.definition can't return null default because that'd be too easy
           var maxRecipients = gdn.definition('MaxRecipients', null);
           if (maxRecipients == 'MaxRecipients') {
               maxRecipients = null;
           }

           $author.tokenInput(gdn.url('/user/tagsearch'), {
               hintText: gdn.definition("TagHint", "Start to type..."),
               tokenValue: 'id',
               tokenLimit: maxRecipients,
               searchingText: '', // search text gives flickery ux, don't like
               searchDelay: 300,
               minChars: 1,
               zindex: 9999,
               prePopulate: author,
               animateDropdown: false,
               ariaLabel: window.gdn.translate("Users"),
               placeholder: window.gdn.translate('Mail or username')
           });
      });
   };

   // Enable multicomplete on selected inputs
   $('.MultiComplete').userTokenInput();

   // Hack: When tokenLimit is reached, hintText will not go away after input is clicked
   // Force it to go away when we click the Body textarea
   $('#Form_Body').click(function() {
      $('.token-input-dropdown').css('display', 'none');
   });

   $('#Form_AddPeople :submit').click(function() {
      var btn = this;
      $(btn).hide();
      $(btn).before('<span class="TinyProgress">&#160;</span>');

      var frm = $(btn).parents('form');
      var textbox = $(frm).find('textarea');

      // Post the form, show the status and then redirect.
      $.ajax({
         type: "POST",
         url: $(frm).attr('action'),
         data: $(frm).serialize() + '&DeliveryType=VIEW&DeliveryMethod=JSON',
         dataType: 'json',
         error: function(xhr, textStatus, errorThrown) {
            $('span.TinyProgress').remove();
            $(btn).show();
            gdn.informError(xhr);
         },
         success: function(json) {
            gdn.inform(json);
            if (json.RedirectTo)
              setTimeout(function() { window.location.replace(json.RedirectTo); }, 300);
         }
      });
      return false;
   });

   gdn.refreshConversation = function() {
       // Get the last ID.
       var conversationID = $('#Form_ConversationID').val();
       var lastID = $('.DataList.Conversation > li:last-child').attr('id');

       $.ajax({
           type: 'GET',
           url: gdn.url('/messages/getnew'),
           data: { conversationid: conversationID, lastmessageid: lastID, DeliveryType: 'VIEW' },
           success: function(html) {
               var $list = $('.DataList.Conversation');
               var $html = $('<ul>'+html+'</ul>');

               $('li.Item', $html).each(function(index) {
                   var id = $(this).attr('id');

                   if ($('#'+id).length == 0) {
                   $(this).appendTo($list).trigger('contentLoad');
                   }
               });
           }
       });
   }

   if (Vanilla.parent)
       Vanilla.parent.refreshConversation = gdn.refreshConversation;
});
