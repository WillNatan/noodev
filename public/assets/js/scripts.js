$(document).ready(function () {
   $('.article-view').each(function () {
       $(this).click(function () {
           $.post('/add-view',
               {
                   articleId: $(this).data('article')
               },
               function(data, status){

               }
               )
       })
   })
});