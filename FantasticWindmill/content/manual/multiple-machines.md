[User Manual](index.html)

# Running LabPal on multiple machines

LabPal runs as a *web server*. When you start a lab at the command line, it launches a process that listens for HTTP requests on a specific port and returns HTTP responses --most of the time in the form of web pages that can be displayed in your browser. We have already seen how to [use the web interface](web-ui.html) to control the execution of experiments and examine their results. We have also seen how you can [run LabPal on a remote machine](web-ui.html#remote). 

We also described how the save/resume feature of LabPal can be used to run different experiments on each machine, and then [merge the save files into a single one](saving.html#merging). This makes it possible, to some extent, to distribute the execution of the lab across multiple machines, and to gather their results and fuse them into a unique, final save file.

However, so far, this process is done by hand. You need to manually start multiple lab instances, manually pick the experiments to run on each, and manually download and merge their respective save files (the merging is actually automatic, but you have to invoke it yourself). This process can be automated further, using the features described in this section.

## <a name="auto-reporting">Auto-reporting</a>

A first useful feature is called *auto-reporting*. Rather than manually download a save file on a machine B, and merge it to the contents on some machine A, one can instruct the lab on machine B to report its results directly to the lab running on machine A, using the HTTP protocol.

In order to do so, you launch the lab instance on machine B using the `--report-to` command-line parameter. Suppose that lab on machine A is reachable as the host `example.com:12345`; then instructing the lab on machine B to report its results to A will be done by launching (on machine B):

<pre><code>java -jar my-lab.jar --report-to example.com:12345</code></pre>

From then on, the lab on machine B will attempt to contact the lab on machine A periodically (by default, every 10 seconds) and tell it to merge its state with the contents that are being sent.

The coupling between A and B is very loose. To start with, A does not need to be started in any special way in order to accept B's updates. As a matter of fact, A does not need to be started at all: if B's attempt to contact A fails, it will simply keep running and try again at the next predefined interval. If A is started in between, it will catch B's update the next time. Moreover, B sends its complete state every time, meaning that if A starts late and misses a couple of updates, it will completely "catch up" with B the next time it is contacted.

This reporting can extend to multiple machines. The following configurations are all possible:

- Multiple labs all report to a single instance A.
- Lab C reports to B, which in turn reports to A. In this case, B's updates to A will also contain C's updates to B.
- Labs A and B report to each other. This can be used to achieve synchronization between the two labs (provided they each run different subsets of experiments).

The periodical reporting is done only when the lab assistant is running. When the lab is not running, no new results are generated, and hence there is no point in repeatedly contacting the other lab when nothing changes. The only exception is when experiments are added or removed from the lab assistant's queue; in that case, an update will be sent immediately, whether the lab is running or not. This makes it possible for lab A to know what experiments are waiting in the queue of some assistant elsewhere.

### Viewing remote results

When auto-reporting is enabled on machine B, the state of experiments can be viewed on machine A:

- An experiment that is queued on machine B will appear on A as "Queued remotely".
- An experiment that is running on machine B will appear on A as "Running remotely".
- An experiment that is finished looks exactly the same, irrespective of where it was run. However, one can still distinguish experiments by giving a different name to the lab assistant on each machine (using the command line parameter `--name`). Each experiment contains the "Run by" metadata element, which contains the name of the lab assistant that executed it.

The "remote" experiments have an "R" next to their status icon in the experiment list. This way, one can easily see what experiments are being queued or are running on other machines, and select *different* experiments to run locally. 

### Setting the update interval

The interval at which updates are sent can be configured at the command line using the `--interval` parameter. Its argument is a number of milliseconds. Hence the following command instructs the lab to report its results every minute:

<pre><code>java -jar my-lab.jar --report-to example.com:12345 --interval 60000</code></pre>

It is recommended to use an interval that is not too short, especially if the lab's save file is large. Each update amounts to a complete cycle of saving/loading, which may take some time.

### Forcing an immediate update

If the `--report-to` argument is used, the *Status* page will display an additional section with information about the reporting of results. If errors occur when attempting to contact the remote lab, they will be displayed there. Also, the section contains a button *Reports results now*; clicking on this button forces a sending of the results immediately, irrepective of the timer for the next update.

## <a name="filtering">Filtering</a>

Auto-reporting automates the task of sending and merging experimental results to a central location. The next feature, filtering, makes it possible to programmatically assign different sets of experiments to each lab instance.

The `--filter` command-line option is used to activate experiment filtering. By default, the argument of this option is a string specifying experiment IDs. Individual IDs are separated by commas, and ranges can also be specified; the syntax is similar to the "page range" text field one can find in a Print dialog box. For example, the following command-line call will launch the lab by including only experiments 1, 3, 4, 5, 6, 10, and 13:

<pre><code>java -jar my-lab.jar --filter 1,3-6,10,13</code></pre>

Experiments whose IDs do not exist are simply ignored. When using the web interface for this lab instance, only the experiments with the selected IDs will be shown. It is important to note that the other, unselected experiments are also instantiated; in other words, they still "exist" in the lab, but are simply not shown.

The filter option makes it easy to start multiple labs, and make sure they run disjoint sets of experiments. For example, suppose a lab has 30 experiments; one can start three instances on three machines, each with a slice of 10 different experiments:

- On machine A: `java -jar my-lab.jar --filter 1-10`
- On machine B: `java -jar my-lab.jar --filter 11-20`
- On machine C: `java -jar my-lab.jar --filter 21-30`

This way, one cannot start an experiment intended to run on another lab instance by mistake.

### Creating custom filters

Filtering by experiment IDs is not always an appropriate way of selecting experiments. After all, IDs are simple numerical identifiers, and experiments with successive IDs are not necessarily related in any meaningful way. If you wish to pick, say, all experiments whose input parameter `a` is equal to 1 or 2, you may have a hard time doing that by choosing their IDs.

The default filter provided by LabPal can be overridden by a filter of your creation. To this end, it suffices to override method [`createFilter`](doc/ca/uqac/lif/labpal/Laboratory.html#createFilter-java.lang.String-) to return an [`ExperimentFilter`](doc/ca/uqac/lif/labpal/ExperimentFilter.html) object. An experiment filter must implement only one method, called `include()`, which, given an experiment, returns true or false, depending on whether this experiment should be selected or not.

The whole point of filtering is to have different lab instances select different experiments depending on some external parameter. The default filter uses a string passed from the command line to decide what experiments to select, and your own filter can do the same. Method `createFilter`, when called, is passed a String object, which is precisely the string obtained from the command line through the `--filter` parameter. It is up to you to do something of that string when you instantiate your filter.

The [LabPal source repository](https://liflab.github.io/labpal), in the `Source/Examples/filtering` folder, shows an example of a custom filter.

## <a name="auto-start">Auto-start</a>

The last piece of the puzzle is the ability to start the execution of experiments without user intervention. This is done with the `--autostart` command line option. This option will launch the lab, directly send all experiments to the lab assistant's queue, and start the assistant. However, if a filter has been defined, only the experiments selected by the filter will be queued.

To recap, consider the following command line:

<pre><code>java -jar my-lab.jar --filter 1-10 --report-to example.com:12345 --interval 60000</code></pre>

This command will start a LabPal instance, automatically run experiments with ID 1 to 10, and report its results every minute to the lab instance at the specified URL.

<!-- :wrap=soft:mode=markdown:maxLineLen=76: -->