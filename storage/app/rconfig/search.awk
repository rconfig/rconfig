BEGIN {
  # https://www.endpoint.com/blog/2017/01/18/using-awk-to-beautify-grep-searches

  # the output of grep in the simple case
  # contains:
  # <file-name>:<line-number>:<file-fragment>
  # let's capture these parts into columns:
  # match : or -
  FS="[:-]"

  # we are going to need to "remember" if the <file-name>
  # changes to print it's name and to do that only
  # once per file:
  file=""

  # we'll be printing line numbers too; the non-consecutive
  # ones will be marked with the special line with vertical
  # dots; let's have a variable to keep track of the last
  # line number:
  ln=0

  # we also need to know we've just encountered a new file
  # not to print these vertical dots in such case:
  filestarted=0
}

# let's process every line except the ones grep prints to
# say if some binary file matched the predicate:
!/(--|Binary)/ {

  # remember: $1 is the first column which in our case is
  # the <file-name> part; The file variable is used to
  # store the file name recently processed; if the ones
  # don't match up - then we know we encountered a new
  # file name:
  if($1 != file && $1 != "")
  {
    file=$1
    print "\n-::" $1 "::-"
    ln = $2
    filestarted=0
  }

  # if the line number isn't greater than the last one by
  # one then we're dealing with the result from non-consecutive
  # line; let's mark it with vertical dots:
  if($2 > ln + 1 && filestarted != 0)
  {
    print "⁞"
  }

  # the substr function returns a substring of a given one
  # starting at a given index; we need to print out the
  # search result found in a file; here's a gotcha: the results
  # may contain the ':' character as well! simply printing
  # $3 could potentially left out some portions of it;
  # this is why we're using the whole line, cutting off the
  # part we know for sure we don't need:
  out=substr($0, length($1 ":" $2 ": "))

  # let's deal with only the lines that make sense:
  if($2 >= ln && $2 != "")
  {
    # sprintf function matches the one found in C lang;
    # here we're making sure the line numbers are properly
    # spaced:
    linum=sprintf("%-4s", $2)

    # print <line-number> <found-string>
    print linum " " out

    # assign last line number for later use
    ln=$2

    # ensure that we know that we "started" current file:
    filestarted=1
  }
}




