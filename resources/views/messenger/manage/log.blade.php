 <!-- Page Heading -->
 <div class="d-sm-flex align-items-center justify-content-between mb-4">
     <h1 class="h3 mb-0 text-gray-200"><?= __tr('Messenger Log') ?></h1>
 </div>
 <!-- Page Heading -->
 <!-- Messenger Log table -->
 <div class="row">
     <div class="col-xl-12 mb-4">
         <div class="card mb-4">
             <div class="card-body">
                 <table class="table table-hover" id="lwMessengerLogDataTable">
                     <thead>
                         <tr>
                             <th><?= __tr('Sender') ?></th>
                             <th><?= __tr('Receiver') ?></th>
                             <th><?= __tr('Message') ?></th>
                             <th><?= __tr('Send On') ?></th>
                         </tr>
                     </thead>
                     <tbody></tbody>
                 </table>
             </div>
         </div>
     </div>
 </div>
 <!-- /Messenger Log table -->
 <script type="text/_template" id="senderProfilePictureTemplate">
     <img class="lw-datatable-profile-picture lw-dt-thumbnail lw-photoswipe-gallery-img" src="<%= __tData.sender_profile_image %>">
    &nbsp<%= __tData.sender %>
</script>

 <script type="text/_template" id="receiverProfilePictureTemplate">
     <img class="lw-datatable-profile-picture lw-dt-thumbnail lw-photoswipe-gallery-img" src="<%= __tData.receiver_profile_image %>">
    &nbsp<%= __tData.receiver %>
</script>

 <script type="text/_template" id="messageTemplate">
     <% if (__tData.type == 1) { %>
    <%= __tData.message %>
<% } else { %>
    <img class="lw-messenger-log-images" src="<%= __tData.message %>">
<% } %>
</script>

 @lwPush('appScripts')
 <script>
     var dtColumnsData = [{
                 "name": "sender",
                 "orderable": true,
                 "template": '#senderProfilePictureTemplate'
             },
             {
                 "name": "receiver",
                 "orderable": true,
                 "template": '#receiverProfilePictureTemplate'
             },
             {
                 "name": "message",
                 "orderable": true,
                 "template": '#messageTemplate'
             },
             {
                 "name": "send_on",
                 "orderable": false,
             }
         ],
         dataTableInstance;

     dataTableInstance = dataTable('#lwMessengerLogDataTable', {
         url: "<?= route('manage.read.messenger_log') ?>",
         dtOptions: {
             "searching": true,
             "order": [
                 [0, 'desc']
             ],
             "pageLength": 25
         },
         columnsData: dtColumnsData,
         scope: this
     });
 </script>
 @lwPushEnd