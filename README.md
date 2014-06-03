biller
======
### ownCloud app implementing billing of customers per used megabyte

By default, each user should enter either credit card information or choose a group to which the billing will be assigned - assuming the user is member of one or several groups with credit card information assigned (see below).

The user preferences on data.deic.dk, should allow assigning a group to a user account (with credit card information).

The admin preferences on a server should allow setting the price on the server in question.

Usage for a given user could for instance be kept in a file in the ownCloud admin directory, which would then have a file for each user. The sharder app would then allow for easy collection of billing data.

For inspiration, see

http://aws.amazon.com/s3/pricing/

http://stackoverflow.com/questions/4746250/how-are-s3-amazon-simple-storage-system-storage-prices-calculated

