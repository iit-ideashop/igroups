SOURCES = \
	student.tex \
	faculty.tex
PDFS = $(SOURCES:%.tex=%.pdf)

all: $(PDFS)

clean:
	-rm -f  $(SOURCES:%.tex=%.aux) \
		$(SOURCES:%.tex=%.log) \
		$(SOURCES:%.tex=%.lof) \
		$(SOURCES:%.tex=%.dvi) \
		$(SOURCES:%.tex=%.toc) \
		$(SOURCES:%.tex=%.out) \

.PHONY: all clean

%.pdf: %.tex
	pdflatex $<
