# Fronius-Inverter-Info-Display
HTML and PHP to pull info from Fronius inverter API and display on tablet screen

Place all files on web server and navigate device used to display output to index.html

HTML calls php script every 4 seconds using jquery. Page self refreshes every 3 hrs using meta refresh tag.

PHP script outputs several div's when it is called, along with style sheet. Unfortunately widths and sizes are hard coded because it was a quick hackjob to get info displaying on one specific device. Scaling viewport in the html should fix most issues or change all size specs manually
