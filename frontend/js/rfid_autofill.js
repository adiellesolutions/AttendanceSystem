(() => {
    let rfidAutofillInterval = null;
  
    function qs(id) { return document.getElementById(id); }
  
    window.startRFIDAutofill = function startRFIDAutofill() {
      if (rfidAutofillInterval) return;
  
      const rfidInput = qs("um_card_uid");
      if (!rfidInput) return;
  
      rfidAutofillInterval = setInterval(() => {
        if (rfidInput.value) return;
  
        fetch("../../backend/api/rfid_latest.php")
          .then(res => res.json())
          .then(data => {
            if (data?.uid) rfidInput.value = data.uid;
          })
          .catch(() => {});
      }, 1000);
    };
  
    window.stopRFIDAutofill = function stopRFIDAutofill() {
      if (rfidAutofillInterval) {
        clearInterval(rfidAutofillInterval);
        rfidAutofillInterval = null;
      }
    };
  })();
  