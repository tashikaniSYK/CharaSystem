# -*- coding: utf-8 -*-  
import sys
from rake_ja import JapaneseRake,Tokenizer
tok = Tokenizer()
ja_rake = JapaneseRake()
text = sys.argv[1]
tokens = tok.tokenize(text)
ja_rake.extract_keywords_from_text(tokens)
print(ja_rake.get_ranked_phrases_with_scores())
