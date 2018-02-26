doc:
	@echo "Now building docs for evias/nem-php"
	@vendor/bin/phpdoc -d src/ -t build/ --template="xml"
	@vendor/bin/phpdocmd build/structure.xml docs/
test:
	@echo "Now testing evias/nem-php NEM Blockchain SDK.."
	@vendor/bin/phpunit
