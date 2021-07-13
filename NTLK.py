# -*- coding: utf-8 -*-  
from rake_ja import JapaneseRake,Tokenizer
tok = Tokenizer()
ja_rake = JapaneseRake()
text = """強力なハンマーを振るう戦士。部下からの人望が厚い。何らかの事情で故郷を離れることになったためか、あまり過去の話をしたがらない。"""
tokens = tok.tokenize(text)
ja_rake.extract_keywords_from_text(tokens)
print(ja_rake.get_ranked_phrases_with_scores())
