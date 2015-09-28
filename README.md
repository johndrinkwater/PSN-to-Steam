PSN ⇔ Steam Games [![Build Status](https://api.travis-ci.org/johndrinkwater/PSN-to-Steam.svg)](https://travis-ci.org/johndrinkwater/PSN-to-Steam)
=========================
This is a community‐sourced database of *Games on Steam mapped to Games on
PSN*, maintained for a future project™.

License
-------
The contents are under
[CC0](https://creativecommons.org/publicdomain/zero/1.0/), which for all
intents and purposes means the public domain.

Contribution
------------
If you want to edit the list, make a fork on GitHub, make your changes and then
make a pull request through GitHub. Describe your changes and be thorough!
Follow the format below, cite sources, etc.

When editing the `GAMES.json` file, remember to follow the format with **tabs
as indentation**, not spaces.  Keep in mind that the list must be sorted
numerically by Steam appid.

**Example**

```json
	…
	"NPWR01719_00":{
		"appid":600,
		"title":"Portal 2",
		"mapping":{
			"1":"ACH.SURVIVE_CONTAINER_RIDE"
		}
	}
```

This example is describing the game Portal 2 (as seen in `"title"`, which is
optional), opening with the PSN ID of `"NPWR01719_00"` quoted as one does in json.
Inside this data field, you SHOULD include an `"appid"` which maps to a Steam
appid, unless this is a duplicate and then you MUST include `"duplicate"`
pointing to the fully described PSN ID mapping of this game.

Next include `"mapping"`, which is a list of ordered numbers of an arbitrary
length. These form a one to one representation to their Steam achievements, and
from peeking at that data they can be numeric (like Binding of Isaac),
prefixed-numeric (like Borderlands 2), and alphanumeric (like Portal 2). Direct
one to one mappings are supported and to avoid accidents, `"mapping"` needs to
be set to false to explicitly confirm this. That means when `"mapping"` is
missing, we do not know how it maps at all. Where the developers have usefully
picked `NAME_trophyid` for their Steam achievements, you should set
`"mapping":"NAME_%d"`

Optionally you can include a `"note"` to give extra details, preferably for the
cases where there are unmappable details with -1 being used.

**Multiple PSN IDs**

To cover the case of multiple PSN IDs mapping onto a singular appid, you create
a new entry and use `"duplicate"` to map to a fully defined mapping.

```json
	…
	"NPWR02081_00":{
		"appid":48000,
		"title":"Limbo",
		"mapping":"ACH_%d"
	},
	"NPWR04612_00":{
		"duplicate":"NPWR02081_00"
	}
```

**Name changes**

In the case where the game has a different name (great example being Dishonored
and Dishonored Definitive Edition, you may include `"title"` in the secondary
definition.

```json
	…
	"NPWR08727_00":{
		"duplicate":"NPWR01767_00",
		"title":"Dishonored Definitive Edition"
	}
```

**Multiple Steam appids**

This example shows a common issue on Steam with titles having multiple
`appid`s, in this case you should make this field a list.

```json
	…
	"NPWR01767_00":{
		"appid":[205100,219460,217980],
		"title":"Dishonored",
```


Commit titles should include `%game% (%appid%)` along with your preferred
flavour text. If you have multiple games to confirm, make individual commits.
Pull Requests must have clean history, no commits to clean previous commits, no
merges that include --no-ff, etc. They will be rejected or rewritten if this is
the case.

Credits
-------
- [johndrinkwater](https://github.com/johndrinkwater) - Creator of The List.
- … and hopefully a whole lot of very helpful people!
- [SteamDB Linux list](https://github.com/SteamDatabase/SteamLinux) - for
  basically giving me a skeleton to build this on
