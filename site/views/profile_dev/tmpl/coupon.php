<html>
    <form id="form_submit">
      <input id="coupon" name="coupon" type="text" size="50" maxlength="13" />
      <input id="btn_submit" type="button" value="Submit">
    </form>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script type="text/javascript">
      $("form_submit").submit(function(e){ e.preventDefault(); return false;});
      $("#btn_submit").click(function(e) { e.preventDefault();
        var coupon = $("#coupon").val();  
        // validate for emptiness
        if(coupon.length < 1 ){ alert("enter coupon value"); }
        else{ 
          $.ajax({
            url: './couponvalidate.php',
            type: 'POST',
            data: {coupon: coupon},
            success: function(result){
              console.log(result);
              if(result){ window.location.href="success.html"; }
              else{ alert("Error: Failed to validate the coupon"); }
            }
          });     
        }
      });
    </script>
  </html>