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
{
	"600" : {
		"id": "NPWR01719_00",
		"title": "Portal 2",
		"mapping": {
			"1":  "ACH.SURVIVE_CONTAINER_RIDE",
			…
	…
}
```

This example is describing the game Portal 2 (as seen in `"title"`, which is
optional), opening with the steam appid of 600, quoted as one does in json.
Inside this data field, you MUST include an `"id"` which maps to a Sony
NP_Title_ID.

Next include `"mapping"`, which is a list of ordered numbers, from 1 up to an
arbitrary length. 0 is reserved. These form a one to one representation to
their Steam achievements, and from peeking at that data they can be numeric
(like Binding of Isaac), prefixed-numeric (like Borderlands 2), and
alphanumeric (like Portal 2).

Optionally you can include a `"note"` to give extra details, preferably for the
cases where there are unmappable details with -1 being used.

**Example**

```json
	…
	"319630" : {
		"id": [ "NPWR07875_00", "NPWR07927_00" ],
		"title": "Life Is Strange™"
	…
```

This example shows a common issue on PSN with titles having multiple `"id"`s, in this case you should make this field a list.


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
