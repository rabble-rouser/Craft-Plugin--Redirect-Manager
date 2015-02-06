var fileContents;
var matchType;
var redirectType;
var redirectTime;
var extension;

function readSingleFile(evt) {
    //Retrieve the first (and only!) File from the FileList object
    var f = evt.target.files[0];

    //get the file extension
    extension = f.name.split('.').pop();

    if (f) {
    var r = new FileReader();
    r.onload = function(e) {
      var contents = e.target.result;
      fileContents = contents;
    }

    r.readAsText(f);
  }
    else {
        alert("Failed to load file");
  }
}

function getMatchType(evt)
{
    matchType = evt.target.value;
}
function getRedirectType(evt)
{
    redirectType = evt.target.value;
}

function getRedirectTime(evt)
{
    redirectTime = evt.target.value;
}

function postForm()
{
    var data = {
        Data: fileContents,
        RedirectType: redirectType,
        MatchType: matchType,
        RedirectTime: redirectTime,
        Ext: extension
    }
    if(!data.Data){
        alert('Please select a file!');
        return false;
    }
    else{
        //console.log(data);
        Craft.postActionRequest('redirectmanager/import', data, function(response) {
            //
          });
    }
}

//todo: see if we can just use one function for these
document.getElementById('file').addEventListener('change', readSingleFile, false);
document.getElementById('string').addEventListener('change', getMatchType, false);
document.getElementById('regex').addEventListener('change', getMatchType, false);
document.getElementById('301').addEventListener('change', getRedirectType, false);
document.getElementById('302').addEventListener('change', getRedirectType, false);
document.getElementById('submitForm').addEventListener('click', postForm, false);
document.getElementById('pre404').addEventListener('change', getRedirectTime, false);
document.getElementById('post404').addEventListener('change', getRedirectTime, false);

