function objectSort(a, b) {
    if (a.name < b.name) {
        return -1;
    }
    if (a.name > b.name) {
        return 1;
    }
    return 0;
}

function sortBy(field) {
    return function(a, b) {
        if (a[field] < b[field]) {
            return -1;
        }
        if (a[field] > b[field]) {
            return 1;
        }
        return 0;
    }
}

function getListOfFiles() {
    var addr = document.location.origin;
    addr += "/wordpress/wp-json/testplugin/api/disk";

    var ret = fetch(addr).then(resp => resp.json())
        .then(json =>{
            var obj = JSON.parse(json);
            return obj;
        });
    
        return ret;
}

function getDiskTree() {
    //make GET Request to my api endpoint that returns the file structure
    var addr = document.location.origin;
    addr = addr + "/wordpress/wp-json/testplugin/api/disk";

    var files = [];

    var ret = fetch(addr)
        .then(resp => resp.json())
        .then(json => {
            //create an area to display the file structure
            var results = document.getElementById("result-area");
            var obj = JSON.parse(json);
            var container = document.createElement("div");
            container.className="dir";
            var list = document.createElement("ul");
            var elems = [];

            let res = [];
            let lvl = {res};

            //give each object it's children if it is a directory
            obj.forEach(path => {
                path.split('\\').reduce((r, name, i, a) => {
                    if(!r[name]) {
                        r[name] = {res: []};
                        r.res.push({name, children: r[name].res})
                    }            
                    return r[name];
                }, lvl)
            })

            //if an object has children, then remove it from the list of files
            //add it to a new list, to allow for directory nesting
            var hasDir = []
            res.forEach((item, index, res) => {
                if (item.children.length > 0) {
                    //check if directory / has children
                    hasDir.push(item);
                    res.splice(index,1);
                }
            }, res)

            //sort each object list alphabetically - this will be changed when sizes are called in and added
            //to the objects, but is good boilerplate for now.
            hasDir = hasDir.sort(sortBy("name"));  
            res = res.sort(sortBy("name"));

            //create elements for each of the items, if it's a subdirectory, assign it the folder class
            //this allows for nest functionality, and also for icons.
            hasDir.forEach((item) => {
                var li = document.createElement("li");
                li.innerText = item.name;
                li.className = "folder";
                elems.push(li);
            })

            //if it's a file, give it the file class.
            res.forEach((item) => {
                var li = document.createElement("li");
                li.innerText = item.name;
                li.className = "file";
                elems.push(li);
            });

            //append the elements to the list to append to the DOM
            elems.forEach((item) => {
                list.appendChild(item);
            });

            //draw the new elements on the DOM
            container.appendChild(list);
            results.appendChild(container);

            hasDir.push(res);

            return hasDir;

        });


    return ret;



}

async function printList(files) {
    var fileList = await files;
    console.log(fileList);
}

async function getDiskSpace(listOfFiles,state) {
    //get a list of files in the file system
    var files = await listOfFiles;
    //calculate the number of files that there are
    var numFiles = files.length;
    //set the initial index for scanning
    var jobState = state;
    //set the url base origin
    var url = document.location.origin;
    //initialise a return array
    var filesizes = [];

    //create a new worker
    worker = new Worker("/wordpress/wp-content/plugins/testplugin/scripts/work.js");
    //add the onmessage listener for when it returns data.
    worker.addEventListener("message", (message) => {
        //add the got filesizes to the return array
        filesizes.push(message.data[1]);
        //print them out for debugging
        console.log(message.data[1]);
        //set the jobstate as the max item reached
        jobState = message.data[0];
        //if there's still more files to go, create another worker
        if (jobState < numFiles) {
            getDiskSpace(files,jobState);
        }
    });
    //send the message to the worker to start scanning
    worker.postMessage({
        jobState,
        url
    });

}

//when the DOM is loaded, add an event listener to the 'Start' button to allow it to make the request.
document.addEventListener("DOMContentLoaded",() => {
    document.getElementById("start").addEventListener("click", () => {
        var files = getDiskTree();
        getDiskSpace(getListOfFiles(),0);
    })
})