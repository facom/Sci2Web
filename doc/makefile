files=$(shell ls *.txt | grep -v util.txt)

html:
	$(foreach file,$(files),make $(file:.txt=.html);)

tex:
	$(foreach file,$(files),make $(file:.txt=.tex);)

dvi:
	$(foreach file,$(files),make $(file:.txt=.dvi);)

pdf:
	$(foreach file,$(files),make $(file:.txt=.pdf);)

%.html:%.txt
	rst2html $^ > $@

%.tex:%.txt
	rst2latex $^ > $(@:.tex=.tex)

%.dvi:%.tex
	latex $^
	latex $^

%.pdf:%.txt
	make $(@:.pdf=.tex)
	make $(@:.pdf=.dvi)
	dvipdf $(@:.pdf=.dvi)

clean:
	rm -rf *.html *.dvi *.aux *.log *.pdf 

