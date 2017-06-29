var curStepN = 0,
    curSalt = null,
    rng = null,
    bsg = null,
    curStepClientInts = [],
    curStepClientBrickTypes = [],
    curStepServerInts = [],
    curStepServerBrickTypes = [];

function verify(salt) {
    // reset the result table and the form
    $("#verifyScreen table tbody, #result").html("");
    
    // fadeover
    $("#initScreen").fadeOut(300);
    window.setTimeout(function() {
        $("#verifyScreen").fadeIn(300);
        $("#saltInput").val("");
 
        // initialize DPRNG and BrickSequenceGenerator
        rng = new DPRNG(salt);
        bsg = new BrickSequenceGenerator(new DPRNG(salt));
        curStepN = 0;
        curSalt = salt;

        // start verification loop
        // initialize the verification on the server
        $.ajax({
            url: '/inspectrng/init',
            data: {
                salt: salt
            },
            success: function(resp)
            {
                if (resp == "ok")
                {
                    verifyNextStep();
                }
                else
                {
                    $("#result").html("Serverfehler.");
                }
            },
            error: function()
            {
                $("#result").html("Serverfehler.");
            }
        });
    }, 350);
}

function verifyNextStep() {
    curStepClientInts = [];
    curStepClientBrickTypes = [];
    
    var currentTR = document.createElement("tr");
    var clientRNGTD = document.createElement("td");
    var clientBSGTD = document.createElement("td");
    var serverRNGTD = document.createElement("td");
    var serverBSGTD = document.createElement("td");
    currentTR.appendChild(clientRNGTD);
    currentTR.appendChild(clientBSGTD);
    currentTR.appendChild(serverRNGTD);
    currentTR.appendChild(serverBSGTD);
    clientRNGTD.innerHTML = clientBSGTD.innerHTML = serverRNGTD.innerHTML = serverBSGTD.innerHTML = "loading...";
    
    $("#verifyTable tbody").append(currentTR);
    currentTR.scrollIntoView(false);
    
    // request the step data from the server
    var sp = new Promise(function(resolve, reject) {
        
        $.ajax({
            url: '/inspectrng/step',
            success: function(data) {
               curStepServerInts = data.rng;
               curStepServerBrickTypes = data.bsg;
               
               serverBSGTD.innerHTML = (curStepN * 100 + 1) + " ... " + ((curStepN + 1) * 100);
               serverRNGTD.innerHTML = (curStepN * 1000 + 1) + " ... " + ((curStepN + 1) * 1000);
               
               resolve();
            },
            error: function() {
                reject();
            }
        });
    });

    for (var i = 0;i < 1000;i++)
    {
        if (i < 100)
        {
            curStepClientBrickTypes.push(bsg.nextType());
        }
        else if (i == 100)
        {
            clientBSGTD.innerHTML = (curStepN * 100 + 1) + " ... " + ((curStepN + 1) * 100);
        }

        curStepClientInts.push(rng.nextInt(0, 6));
    }

    clientRNGTD.innerHTML = (curStepN * 1000 + 1) + " ... " + ((curStepN + 1) * 1000);
    
    sp.then(function() {
        var error = false;
        for (var i = 0;i < 1000;i++)
        {
            if (curStepClientInts[i] != curStepServerInts[i])
            {
                $(clientRNGTD).addClass("error").append(": " + i);
                $(serverRNGTD).addClass("error").append(": " + i);
                
                $("#result").append("Client and Server out of sync with random numbers at "
                    + (curStepN * 1000 + i + 1) + " integers [0, 7]:<br>"
                    + "server: " + curStepServerInts[i]
                    + ", client: " + curStepClientInts[i] + "<br>");
                document.getElementById("result").scrollIntoView(false);
                error = true;
                break;
            }
        }
        if (!error) $([clientRNGTD, serverRNGTD]).addClass("verified");
        
        for (var i = 0;i < 100;i++)
        {
            if (curStepClientBrickTypes[i] != curStepServerBrickTypes[i])
            {
                $(clientBSGTD).addClass("error").append(": " + i);
                $(serverBSGTD).addClass("error").append(": " + i);

                $("#result").append("Client and Server out of sync with brick types at "
                    + (curStepN * 100 + i + 1) + " bricks:<br>"
                    + "server: " + curStepServerBrickTypes[i].toUpperCase()
                    + ", client: " + curStepClientBrickTypes[i].toUpperCase());
                document.getElementById("result").scrollIntoView(false);
                return;
            }
        }
        $([clientBSGTD, serverBSGTD]).addClass("verified");
        
        if (curStepN >= 499)
        {
            // 50 steps without error => everything okey
            $("#result").html("Client and Server seem to be in sync with salt " + curSalt);
            document.getElementById("result").scrollIntoView(false);
        }
        else
        {
            // next step
            curStepN++;
            verifyNextStep();
        }
    }, function(e) {
        console.log(e);
        $("#result").html("Serverfehler.");
    });
}