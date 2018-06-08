# Contribution guidelines

If you'd like to help with developing JsonMapper, you are welcome. Please consider these guidelines when offering your work.

## Honor the existing code style

When reviewing your pull request, your improvement or fix should stand out clearly. Try to not add or remove whitespace that 
has nothing to do with your core work.

This repository is following PEAR coding style. The parts of the code that are not defined in PEAR are best kept as-is.
Especially, do not align assignments. It's an additional effort to maintain, and also adds changed lines to pull request if the 
alignment should ever change again (e.g. due to a variable renaming etc.).

## Keep the external dependencies close to zero

Adding dependencies in order to fulfil a job has to have a tremendous benefit compared to the drawback that every use of `JsonMapper` 
now has to be compatible with the additional dependency. Check with the maintainers of this project before investing a lot of work
if it would be ok to add a dependency.

## If something was broken and you fixed it, please add a test for this

The test help communicating what kind of error or undefined behaviour you experienced, and it helps verifying it is correctly fixed
from now on.
