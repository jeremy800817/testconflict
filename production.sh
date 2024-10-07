function merge() {  
    SOURCE=$1
    DEST=$2
    TARGET="/usr/local/nginx/html/gtp"

    echo
    echo "---> Merging $SOURCE with $DEST" 

    echo
    echo "---> ---> Checkout $DEST ..."     
    git checkout $DEST

    echo
    echo "---> ---> Pull $DEST ..."
    if ! git pull --ff-only origin $DEST
    then
        echo 'error on conflict, no Pull'
        rm source/snapapp/classic.json
        rm source/snapapp/classic.jsonp
        rm source/snapapp/index.html
        unlink source/snapapp/classic
        unlink source/snapapp/resources
        
        if git pull --ff-only origin $DEST
        then
            rm source/snapapp/classic.json
            rm source/snapapp/classic.jsonp
            rm source/snapapp/index.html
            rm -r source/snapapp/classic
            rm -r source/snapapp/resources
            ln -s ./src/build/production/snap/classic.json ./source/snapapp/classic.json
            ln -s ./src/build/production/snap/classic.jsonp ./source/snapapp/classic.jsonp
            ln -s ./src/build/production/snap/index.html ./source/snapapp/index.html
            ln -s ./src/build/production/snap/classic ./source/snapapp/classic
            ln -s ./src/build/production/snap/resources ./source/snapapp/resources
            else
            echo "error on pull after rm production files. check manually"
            exit 1
        fi
    fi

    # echo
    # echo "---> ---> Merging $SOURCE with $DEST ..." 
    # # --ff-only trigger errors if merge/pull is not possible
    # if ! git merge --ff-only $SOURCE --no-edindexit
    # then
    #     echo 'error on conflict, no merge'
    #     # rm source/snapapp/classic.json
    #     # rm source/snapapp/classic.jsonp
    #     # rm source/snapapp/index.html
    #     # rm source/snapapp/src/app.js
    #     # unlink source/snapapp/src/classic
    #     # unlink source/snapapp/src/resources
    #     exit 1
    # else
    #     echo 'NO conflict'
    #     exit 1
    # fi

    # echo
    # echo "---> ---> Push $DEST ..."
    # git push origin $DEST
}

function deploy() {
    MODE=$1
    SOURCE_BRANCH=$2

    echo     
    echo "---> Pull changes from Master ..."

    if ! git checkout $SOURCE_BRANCH
    then    
        exit 1
    fi

    git pull --progress --no-edit --no-stat -v --progress origin master

    if ! merge $SOURCE_BRANCH
    then
        echo "error on merge"
        exit 1
    fi

    # if ! merge $SOURCE_BRANCH 'staging'
    # then      
    #   exit 1
    # fi

    # if [ $MODE = "live" ]
    # then
    #     if ! merge $SOURCE_BRANCH 'master'
    #     then          
    #       exit 1
    #     fi

    #     if ! merge 'master' 'production'
    #     then          
    #       exit 1
    #     fi
    # fi
}

MODE=$1
SOURCE_BRANCH=$2

if [ -z "$MODE"  -o -z "$SOURCE_BRANCH" ]
then
    echo "Usage:"
    echo ""
    echo  "MODE BRANCH_NAME (MODE: live|staging)"
else
    if git show-ref --verify --quiet "refs/heads/$SOURCE_BRANCH"
    then
        echo
        echo "### START ###"
        echo
        deploy $MODE $SOURCE_BRANCH
        echo
        echo "### END ###"
        echo
    else
        echo
        echo "Error: Branch $SOURCE_BRANCH not found"
    fi
fi