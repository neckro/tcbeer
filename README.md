# twincitiesbeer.com site

This was my first PHP site, for a half-assed startup I did with a friend of mine.

The idea: Have a database of which bars and restaurants served which beers, so that beer aficionados could run a search and find out where to get their favorite brews.  We actually had this working, but due to inexperience on my part and a general lack of interest it fizzled out.

I wrote this code between 2006 and 2007 as a totally green Web developer.  It's for historical/amusement purposes only!  Don't actually try to use this code.

Things I didn't really understand when I wrote it include:

* Version control
* Templating
* Object-oriented PHP (the code is reasonably well-organized, considering)
* MVC principles (I tried to separate them, but without OO or templates it was a losing battle)
* mysqli (although I make an admirable attempt at sanitizing all input)
* Any kind of PHP framework at all

Things I almost actually got right:

* Locational search worked, and there's an attempt at a natural-language query parser.  You could type "surly in 55413" and it would find all Surly beers in the 55413 area code, for example.  Still kinda proud of that one.
* Pretty URLs using Apache rewrites

Things missing from the repository:

* The TCBeer blog site (Wordpress)
* Database schema

Enjoy!  Or don't.

- Joseph Culbert (neckro@decay.us)
