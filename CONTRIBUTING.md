# Contributing

So you'd like to contribute to the `CaptainHook` library? Excellent! Thank you very much. I can absolutely use your help.

- [Getting started](#Getting-started)
	- [Look for existing issues](#Look-for-existing-issues)
	- [Open a new issue before making a PR](#Open-a-new-issue-before-making-a-PR)
	- [Write a use case](#Write-a-use-case)
- [Coding standards](#Coding-standards)
	- [Tests](#Tests)
	- [Style and formatting](#Style-and-formatting)
- [Documentation](#Documentation)
	- [Write doc comments](#Write-doc-comments)
	- [Update the docs](#Update-the-docs)
- [Before submitting your pull request](#Before-submitting-your-pull-request)
- [After submitting your PR](#After-submitting-your-PR)
- [The code review process](#The-code-review-process)
	- [Expect to be taken seriously](#Expect-to-be-taken-seriously)
	- [Dealing with comments](#Dealing-with-comments)
	- [This may take a while](#This-may-take-a-while)

# Getting started

Fork the project to your own github account. Clone the forked repository to your development machine.
Then run the following two commands to setup the project.

    $ composer install
    $ tools/phive --home ./.phive install --trust-gpg-keys 4AA394086372C20A,31C7E470E2138192,8E730BA25823D8B5 --force-accept-unsigned

Here are some hints on a good workflow for contributing to the project.

## Look for existing issues

First of all, check the [issues](https://github.com/captainhook-git/captainhook/issues) list.
If you see an outstanding issue which you would like to tackle, by all means comment on the
issue and let me know.

If you already have an idea for a feature you want to add, check the issues list anyway,
just to make sure it hasn't already been discussed.

## Open a new issue before making a PR

I _don't_ recommend just making a pull request for some new feature. Usually it's better
to [open an issue](https://github.com/captainhook-git/captainhook/issues/new) first,
and we can discuss what the feature is about, how best to design it, other people can
weigh in with contributions, and so forth. Design is, in fact, the hard part. Once we
have a solid, well-thought-out design, implementing it is usually fairly easy.
(Implementing a bad design may be easy too, but it's a waste of effort.)

## Write a use case

This is probably the most important thing to bear in mind. A great design principle for
software libraries is to start with a real-world use case, and try to implement it
using the feature you have in mind. _No issues or PRs will be accepted into `CaptainHook`
without an accompanying use case_.

The reason for insisting on this up front is that it's much easier to design a feature
the right way if you start with its usage in mind. It's all too easy to design something
in the abstract, and then find later that when you try to use it in a program,
the API is completely unsuitable.

Another reason for having a use case is that it gives us a useful example program,
which can be included with the documentation to show how the feature is used.

The final reason is that it's tempting to over-elaborate a design and add all sorts
of bells and whistles that nobody actually wants. Simple APIs are best. If you think
of an enhancement, but it's not needed for your use case, leave it out.
Things can always be enhanced later if necessary.

# Coding standards

A library is easier to use, and easier for contributors to work on, if it has a consistent,
unified style, approach, and layout. Here are a few hints on how to make a `CaptainHook` PR
which will be accepted right away.

## Tests

It goes without saying, but I'll say it anyway, that you must provide comprehensive tests
for your feature. Code coverage should be 100%.

Test data should go in the `test/files` directory.

### Spend time on your test cases

Add lots of test cases; they're cheap. Don't just test the obvious happy-path cases; test the
null case, where your feature does nothing (make sure it does!). Test edge cases, strange
inputs, missing inputs, non-ASCII characters, zeroes, and nils. Knowing what you know
about your implementation, what inputs and cases might possibly cause it to break? Test those.

Remember people are using `CaptainHook` in their daily development process
where their data, their privacy, and even their business could be at stake. Now, of course
it's up to them to make sure that their programs are safe and correct; library maintainers
bear no responsibility for that. But we can at least ensure that the code is as reliable
and trustworthy as we can make it.

## Style and formatting

This is easy, just use the PSR12 coding standard.

Your code should pass `phpstan` and `phpcs` without errors
(and if you want to run other linters too, that would be excellent).

# Documentation

It doesn't matter if you write the greatest piece of code in the history of the world,
if no one knows it exists, or how to use it.

## Write doc comments

Any functions or methods you write should have useful documentation comments in the standard `PHPDoc`
format. Specifically, they should say what inputs the function takes, what it does (in detail),
and what outputs it returns. If it returns an error value or throw an exception,
explain under what circumstances this happens.

## Update the docs

Any change to the `CaptainHook` feature addition should also be accompanied by an update to the documentation.
If you add a new feature, add it in the documentation as well.

# Before submitting your pull request

Here's a handy checklist for making sure your PR will be accepted as quickly as possible.

 - [ ] Have you opened an issue to discuss the feature and agree its general design?
 - [ ] Do you have a use case and, ideally, an example program using the feature?
 - [ ] Do you have tests covering 100% of the feature code (and, of course passing)
 - [ ] Have you written complete and accurate doc comments?
 - [ ] Have you updated the documentation if necessary?
 - [ ] You rock. Thanks a lot.

# After submitting your PR

Here's a nice tip for PR-driven development in general. After you've submitted the PR, do a 'pre-code-review'.
Go through the diffs, line by line, and be your own code reviewer. Does something look weird?
Is something not quite straightforward? It's quite likely that you'll spot errors at this stage which
you missed before, simply because you're looking at the code with a reviewer's mindset.

If so, fix them! But if you can foresee a question from a code reviewer, comment on the code to answer
it in advance. (Even better, improve the code so that the question doesn't arise.)

# The code review process

If you've completed all these steps, I _will_ invest significant time and energy in giving your PR a
detailed code review. This is a powerful and beneficial process which can not only improve the code,
but can also help you learn to be a better engineer and a better programmer - and the same goes for me!

## Expect to be taken seriously

Don't think of code review as a "you got this wrong, fix it" kind of conversation
(this isn't a helpful review comment). Instead, think of it as a discussion where both sides can ask
questions, make suggestions, clarify problems and misunderstandings, catch mistakes, and add improvements.

You shouldn't be disappointed if you don't get a simple 'LGTM' and an instant merge.
If this is what you're used to, then your team isn't really doing code review to its full potential.
Instead, the more comments you get, the more seriously it means I'm taking your work.
Where appropriate, I'll say what I liked as well as what I'd like to see improved.

## Dealing with comments

Now comes the tricky bit. You may not agree with some of the code review comments.
Reviewing code is a delicate business in the first place, requiring diplomacy as well as discretion,
but responding to code reviews is also a skilled task.

If you find yourself reacting emotionally, take a break. Go walk in the woods for a while, or play
with a laughing child. When you come back to the code, approach it as though it were someone else's,
not your own, and ask yourself seriously whether or not the reviewer _has a point_.

If you genuinely think the reviewer has just misunderstood something, or made a mistake,
try to clarify the issue. Ask questions, don't make accusations. Remember that every project
has a certain way of doing things, which may not be your way. It's polite to go along with these
practices and conventions.

You may feel as though you're doing the project maintainer a favour by contributing, as indeed you are,
but an open source project is like somebody's home. They're used to living there, they probably
like it the way it is, and they don't always respond well to strangers marching in and rearranging the furniture.
Be considerate, and be willing to listen and make changes.

## This may take a while

Don't be impatient. We've all had the experience of sending in our beautifully-crafted PR and then waiting,
waiting, waiting. Why won't those idiots just merge it? How come other issues and PRs are getting dealt
with ahead of mine? Am I invisible?

In fact, doing a _proper_ and serious code review is a time-consuming business. It's not just a case
of skim-reading the diffs. The reviewer will need to check out your branch, run the tests,
think carefully about what you've done, make suggestions, test alternatives.
It's almost as much work as writing the PR in the first place.

Open source maintainers are just regular folk with jobs, kids, and zero free time or energy.
They may not be able to drop everything and put in several hours on your PR.
The task may have to wait a week or two until they can get sufficient time and peace and
quiet to work on it. Don't pester them. It's fine to add a comment on the PR if you haven't
heard anything for a while, asking if the reviewer's been able to look at it and whether
there's anything you can do to help speed things up. Comments like 'Y U NO MERGE' are unlikely
to elicit a positive response.

Thanks again for helping out!
