#! /bin/bash

####################################################
# Handles linking & unlinking of softlinks for UAT #
####################################################


unlink_and_discard () {
  echo "Unlinking soft links..."
  ## Unlink soft links and restore overwritten files
  rm ./source/snapapp/classic.json && git checkout -- ./source/snapapp/classic.json
  rm ./source/snapapp/classic.jsonp && git checkout -- ./source/snapapp/classic.jsonp
  rm ./source/snapapp/index.html && git checkout -- ./source/snapapp/index.html
  rm ./source/snapapp/classic
  rm ./source/snapapp/resources

  echo "Discarding tracked build files..."
  ## Discard tracked production build files to prevent conflicts with build files from GTP core
  git checkout -- source/snapapp/src/build/production/
  git checkout -- source/snapapp/src/build/production/snap/classic.json
  git checkout -- source/snapapp/src/build/production/snap/classic.jsonp
  git checkout -- source/snapapp/src/build/production/app.js
  git checkout -- source/snapapp/src/build/production/snap/classic/resources/css/devcss.css
  git checkout -- source/snapapp/src/build/production/snap/resources/css/devcss.css

  echo "Completed unlink_and_discard"
}


link_build_files () {
  echo "Linking production build files"
  ln -sf ./source/snapapp/src/build/production/snap/classic.json ./source/snapapp/classic.json
  ln -sf ./source/snapapp/src/build/production/snap/classic.jsonp ./source/snapapp/classic.jsonp
  ln -sf ./source/snapapp/src/build/production/snap/index.html ./source/snapapp/index.html
  ln -sf ./source/snapapp/src/build/production/snap/classic ./source/snapapp/classic
  ln -sf ./source/snapapp/src/build/production/snap/resources ./source/snapapp/resources
  echo "Finished linking production build files"
}

pull_latest_changes() {
    echo "Pulling latest changes from $CURRENT_BRANCH"
    local success=1
    local current_commit=`git log --oneline -1 | awk '{print \$1}'`

    echo "Saving stash"
    git stash -a

    if [ git pull --ff-only origin $CURRENT_BRANCH ]; then
      echo "Successfully pulled latest changes from build branch"
    else
      echo "Failed pulling latest changes from build branch"
      success=0
    fi

    if [ $success -ne 1 ]; then
      echo "Aborting.."
      git merge --abort
      git reset --hard $current_commit
      git stash pop
      exit 1
    fi

    echo "Reapplying stash"
    git stash pop
}

# Not used for now, currently manually building on local
build() {
  # Only do releases if build branch is currently checked out
  if [ $CURRENT_BRANCH = $BUILD_BRANCH ]; then
    unlink_and_discard

    # Pull latest build
    if [ git pull --ff-only origin $BUILD_BRANCH ]; then
      echo "Pull"
    fi

  #  merge_and_tag_release

    link_build_files

  else
    echo "Current branch is ${CURRENT_BRANCH}. Please make sure the current branch is appointed build branch."
    echo "If this message is an error, please edit the script and retry."
    exit 1
  fi
}

deploy() {
  echo "0"
}


###########################################################################

BUILD_BRANCH="release/mygtp/build"
RELEASE_BRANCH="release/mygtp/release"
CURRENT_BRANCH=`git branch --show-current`

if [ $# -ne 0 ]; then
  echo "Helper script to link build files into snapapp dir. No arguments needed"
  echo "Please edit script file if there are any changes to deployment process"
  echo ""
  echo "Usage: ./script.sh"
  echo "Example: /bin/bash $0"
  exit 1
fi

unlink_and_discard
pull_latest_changes
link_build_files

## Vim configuration
# vim: set et sw=2 ts=2 :
