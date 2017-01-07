[User Manual](index.html)

# Adding metadata

An important part of creating experiments with LabPal is to enable an external user to re-run them easily. To this end, it is crucial to properly **document** your experimental setup, so that other people can understand what you are experimenting, and what all the values, tables and plots mean.

LabPal offers various ways to define *metadata* for your lab --that is, data about its data. You can set descriptions for:

- [The lab](#lab)
- [Each experiment group](#group)
- [Each experiment](#experiment)
- [Each parameter](#parameters)

## <a name="lab">Lab description</a>

A first metadata is about the lab itself. You can enter a textual description for the lab using method `setDescription`:

<pre><code>
public void setup() {
  ...
  setDescription("This is a lab for comparing sorting algorithms.");
}
</code>
</pre>

If a description is defined, it will be displayed in the web console in the *Home* page, replacing the default help text that shows up otherwise. This description can include any valid HTML markup, and can be as long as you wish.

A recommended tip is to write the description in a separate HTML file that you place in the same folder as your source code. You can then use `FileHelper` to load that file's contents into the description:

<pre><code>
public void setup() {
  ...
  setDescription(FileHelper.internalFileToString("description.html",
    this.getClass());
}
</code>
</pre>

## <a name="group">Group description</a>

If the lab has any experiment [groups](experiment.html#groups), each group can be given a description using the `setDescription` method:

<pre><code>
public void setup() {
  ...
  Group g = new Group("Gnome Sort");
  g.setDescription("Experiments that use the Gnome sort algorithm");
  ...
}
</code>
</pre>

The group description, if any, is displayed in the experiment list, under the group's name.

## <a name="experiment">Experiment description</a>

A description can be entered for each experiment separately. This can be done in two ways:

- Using the experiment's `setDescription` method to set the text
- By overriding the `getDescription` method to return whatever text you want

In both cases, the description string should be valid HTML. This description is displayed in the Experiment page.

The description can be made dynamic, and depend on the experiment's input parameters. In the sorting example we used throughout this manual, one could hence write:

<pre><code>
public String getDescription() {
  return "Sorts an array of size " + readInt("Size") +
    " using " + readString("Algorithm");
}
</code>
</pre>

## <a name="parameters">Parameter description</a>

Each individual parameter (input and output) can also be given a description. To this end, an experiment can use the method `describe`:

<pre><code>
public abstract class SortExperiment extends Experiment {
  public SortExperiment(int n) {
    setInput("Size", n);
    describe("Size", "The size of the array to sort");
    describe("Time", "The time (in ms) it takes to sort the array");
    describe("Algorithm", "The algorithm used to sort the array");
  }
  ...
}
</code>
</pre>

One can see how the `describe` method has been used to associate a short description of the input parameter "Size", as well as the output parameter "Time" and the input parameter "Algorithm". These last two parameters have not yet received a value, but they can already get a description.

In the web console, the description of parameters shows as tooltips wherever the parameter name appears (except in tables). Hovering the mouse over the name displays the description. This makes it easy for an external user to understand the meaning of each value, and in particular to know the units (if any) used for that value.

<!-- :wrap=soft:mode=markdown: -->