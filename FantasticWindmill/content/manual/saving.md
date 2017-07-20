[User Manual](index.html)

# Saving, loading, merging labs

Our examples so far have involved relatively short experiments that could run within a few seconds. However, the labs you will create will likely contain more than a handful of experiments, whose runing time may take minutes, if not hours. For all sorts of reasons, you might not want to run all these experiments in a single pass. It would be nice if you could select and run a few of them, close your setup (and even your computer) and run more experiments at a later time.

If you used command-line batch scripts, implementing this kind of pause-resume feature would require some non-trivial work on your part. How do you persist the state of the experiments? How can you easily see what's done and what's not? However, if you use LabPal, these features are possible in a few clicks.

In this section, you will learn to use the following features:

- [Save a lab](#saving)
- [Load a lab](#loading)
- [Merge labs](#merging)
- [Preload a lab with results stored internally](#preloading)
- [Export a lab notebook](#notebook)
- [Be careful with serialization](#serialization)

## <a name="saving">Saving a lab</a>

Saving the current state of a lab can be done by going to the *Status* page and clicking on the button *Save lab data*. Your web browser will prompt you to select a location on your computer where the file can be saved, as with any fil you download from the web. By default, the file's name matches the one given to the lab (using the [`setTitle`](doc/ca/uqac/lif/labpal/Laboratory.html#setTitle-java.lang.String-) method.

By convention, a LabPal save file has the extension `.labo`. For each experiment, this file saves all of its input parameters, the output parameters it generated (if any), as well as its current status (finished or not). If any experiment is running at the time the lab is saved, it will be saved into the file as Ready (i.e. not executed). LabPal cannot save and restore experiments that are in the middle of their execution.

### What's in a `.labo` file?

That file is simply a ZIP archive with a different file extension. As a matter of fact, if you rename a file like `mylab.labo` into `mylab.zip`, you should be able to open it with any software that handles ZIP files.

The contents of the archive is a single other file, in the [JSON](http://json.org/) format. Among other things, you can find in this file every input/output parameter of every experiment in the lab, as well as additional metadata such as experiments' start/stop time, status, etc.

Since this file is a plain JSON document, it is easily amenable to external processing by scripts or software other than LabPal. By loading this file into a JSON parser in the language of your choice, you should be able to extract any data inside the lab with little effort. This way, your lab results are not "trapped" inside LabPal and can be used elsewhere.

While this file can be *read* and reused externally, it is not recommended to *edit* this file and try to reload it in LabPal. Any changes in the structure or the type of some of its fields may result in LabPal refusing to read it again.

## <a name="loading">Loading a lab</a>

Loading a lab is the reverse operation: given a previously saved `.labo` file, LabPal will read and restore each experiment in the state it was when the lab was saved. A lab can be loaded either through the web interface, or when launching it at the command line.

### Using the web interface

This can be done by going to the *Status* page, and first clicking the *Browse* button to select a file on your local computer. Then, press the *Upload* button to upload the file to the lab. If everything goes without error, the lab's current state will be overwritten with the contents of the file.

Please note that this overwrite is exactly that: a complete overwrite. If your current lab has an experiment that is finished, and the file you load has that same experiment in the "not done" status, the results of the experiments will be wiped and replaced by the contents of the file. If you want to overwrite only experiments that are not run in your current lab, the functionality you are looking for is called [merging](#merging).

Since LabPal can be started on some machine A, and then be accessed through a browser on machine B, this feature can be used to upload a save file on B to the server instance on A.

### Using the command line

Alternately, one can load a lab from a file directly when launching LabPal. This can be done by simply specifying a filename at the command line:

<pre><code>java -jar mylab.jar somefile.labo</code></pre>

Unlike the web interface, the file you specify (obviously) has to reside on the same machine as the lab.

## <a name="merging">Merging labs</a>

As we have said, loading a lab completely overwrites its state. However, one can also *merge* two labs together. Suppose your current lab is called A, and you wish to merge the contents of another instance of the lab, called B. Merging will be done according to the following rule: if an experiment is finished in B, and is not executed in A, the contents of B for that experiment will overwrite those in A.

Merging can be done on the command line, by simply specifying more than one filename:

<pre><code>java -jar mylab.jar somefile1.labo somefile2.labo ...</code></pre>

In such a case, an empty lab is started; this lab is then merged with the contents of `somefile1.labo`; the resulting lab is then merged with the contents of `somefile2.labo`, and so on. One should remark that the order in which the files matters: if an experiment is executed in `somefile1` and `somefile2`, the contents of `somefile2` for that experiment will *not* override those of `somefile1`, as per the rule mentioned above.

In most civilized command line shells, you can also use wildcards instead of an explicit list of files:

<pre><code>java -jar mylab.jar *.labo</code></pre>

This merging functionality makes the following thing possible, among others:

1. Start multiple instances of the same lab, either on the same machine or on multiple machines
2. Run distinct experiments on each of these instances
3. Save the lab's state for each of these instances in different files
4. Load a new lab and merge all these files
5. Optionally, save the resulting lab in a final, global file

As you can see, this can be used to distribute the execution of a lab to different people, and to combine their results at a later time. This process can be further automated using the [auto-reporting](#) functionality.

## <a name="preloading">Preload a lab with results stored internally</a>

If you write a research paper, it might be desirable to put online a copy of your lab, which contains the exact results you refer to in the paper. One possible way is to also put online the save file corresponding to the lab, as described above. A user can then download the lab, and (through the command line or the web interface), load the save file to retrieve the data. This way, any link in the paper to a table cell or a plot will point in the lab to the exact same value or plot (see [Data Provenance and Traceability](provenance.html)).

However, LabPal can also be setup to load this data automatically, without the need for user intervention. In order to do so, you must place the save file *within* the JAR bundle that you create and distribute.

Let us give an example using the LabPal [template project](https://github.com/liflab/labpal-project). Suppose you run all the experiments in this project, and save its state in the file `mylab.labo`. You should place that file inside your project, in the **same** folder that contains the source file of the laboratory class. Here, the source file `MyLaboratory.java` is in the `src` folder, so the `.labo` file must be put there also. Be careful if you use packages within the project: each package level corresponds to a subfolder; always make sure that the save file and the lab class source are side by side.

These contents can be loaded when the lab starts, using the `--preload` command-line option. Suppose that `my-lab.jar` is the JAR file created from the lab's project; then typing the following will start the lab and preload it with the contents of `mylab.labo` immediately:

<code><pre>java -jar my-lab.jar --preload</pre></code>

This way, it is not necessary to ship a separate file containing the lab's experimental results. Note that the user can still choose to start an empty lab by removing the `--preload` option.

By default, LabPal will look for every `.labo` file that is found in the folder; if multiple such files are found, they will be all merged together at startup. Look out for old files that are no longer in use, as they could mess up with your data or event prevent it from being loaded (see below).

If you call `--preload` and no file is found, a message will be displayed at the command line and LabPal will start with an empty lab instead.

## <a name="notebook">Export a lab notebook</a>

Suppose you want to show your experimental results (plots, tables, or individual values) to another person, without requiring this person to download and start your lab with the [`--preload` feature](#preloading). You can do this by creating a **lab notebook**.  To create a notebook, go to the *Status* page in the web interface and click on the "Save notebook" button. The generation of the notebook can take a few seconds, depending on the size of your lab, so please be patient.

A notebook is a ZIP archive, with the contents of all the pages in the web interface, saved as static HTML files. A person that receives such a notebook can unzip the file, open `index.html` in a web browser, and from then on, navigate a static version of your lab's web interface. Most buttons in this interface still work: for example, clicking on the *Plots* button will take you to the Plots page, showing all the plots in the state they were when the notebook was created. The same is true of the *Experiments*, *Tables* and *Macros* pages: a user can still click on individual experiments or tables, and see their contents. This way, it is possible to save and pass around a "read-only" version of the web interface.

Since the notebook is just a folder of static images and HTML files, it can also be uploaded to a web server. However, since no lab is running, some of LabPal features obviously don't work: experiments cannot be sent to the lab assistant, and the [data provenance](provenance.html) features are not accessible. In the web interface, buttons that do not work within a notebook are greyed out and have no effect when clicked. The web interface also displays a message on each page, reminding users that they are not running an actual lab, but are merely looking at a static snapshot that was generated beforehand.

The lab notebook feature is intended for *viewing* results by a person. If your goal is to export the lab's data to process it externally, you should rather consider using the `.labo` save file, or export tables as CSV text files (both features are available in LabPal).

## <a name="serialization">Be careful with serialization</a>

LabPal saves the status of experiments through a process called *serialization*. Serialization stores into a file, for each `Experiment` object, the exact class name and contents of each member field of that object. A few precautions must be taken when using LabPal's Save feature.

### Objects must match

In order for an experiment to be replaced by some contents on file, the object structure (i.e. name and type of each member field, and so on recursively for their values) must match **exactly**.

This means that if you create an `Experiment` class, run and save a lab, and then modify the structure of that class and attempt to load the original file, chances are you will receive an error message saying that the lab cannot be loaded. This is because LabPal will try to set values to fields that no longer exist, or have a different type from those that were saved in the first place.

Although there exist situations in which you will still be able to load the file, we do not recommend to rely on that trick in normal circumstances.

### Use transient fields when possible

Sometimes, an experiment will have member fields whose contents does not need to be saved. Consider the following experiment:

<pre><code>class MyExperiment extends Experiment {
  
  private MyOtherClass c;
  
  public MyExperiment() {
    c = new MyOtherClass();
  }
  ...
}</code>
</pre>

Here, the contents of field `c` do not need to be saved: this field is given a value in the experiment's constructor, and that value depends on nothing. Hence, rather than saving the complete state of `c` (which could be a large object) into the file, one can declare this field using the Java keyword `transient`. This will indicate LabPal that this object can be skipped when saving `MyExperiment` (and will similarly be skipped when loading the experiment).

As a rule, we encourage the use of the `transient` keyword for any field whose exact state is not necessary to reconstruct an experiment from file. This contributes to reducing the size of your save files, and may avoid deserialization problems.

### Define a no-args constructor

LabPal recreates experiments from a file by first checking what class the experiment belongs to, and then calling that class' "no-args" constructor --its constructor with no arguments. Once an object is created, its member fields are then filled with the contents of the file.

This means that, for a class to be able to be reloaded form a save file, it must define a no-args constructor. This constructor does not need to do anything meaningful (it can even be declared `private`), but it must exist for the deserialization process to work properly. If this is not the case, a warning message to this effect will be displayed in the web interface, in the *Status* page.

<!-- :wrap=soft:mode=markdown:maxLineLen=76: -->